<?php

/**
 *
 *    Copyright (C) 2016 onOffice Software AG
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2016, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

use onOffice\WPlugin\Form;
use onOffice\WPlugin\FormData;
use onOffice\WPlugin\FormPost;
use onOffice\SDK\onOfficeSDK;

class FormPostApplicantSearch
	extends FormPost
{

	/** */
	const LIMIT_RESULTS = 100;


	/**
	 *
	 * @param string $prefix
	 * @param in $formNo
	 *
	 */

	protected function analyseFormContentByPrefix($prefix, $formNo = null) {
		$formConfig = ConfigWrapper::getInstance()->getConfigByKey('forms');

		$configByPrefix = $formConfig[$prefix];
		$formFields = $configByPrefix['inputs'];

		$limitResults = self::LIMIT_RESULTS;

		if (array_key_exists('limitResults', $configByPrefix))
		{
			if ($configByPrefix['limitResults'] > 0)
			{
				$limitResults = $configByPrefix['limitResults'];
			}
		}

		$newFormFields = $this->getFormFieldsConsiderSearchcriteria($formFields);

		$formData = array_intersect_key($_POST, $newFormFields);

		$pFormData = new FormData($prefix, $formNo);
		$pFormData->setRequiredFields($configByPrefix['required']);
		$pFormData->setFormtype($configByPrefix['formtype']);

		$this->setFormDataInstances($prefix, $formNo, $pFormData);

		$pFormData->setValues($formData);

		$missingFields = $pFormData->getMissingFields();

		if (count($missingFields) > 0)
		{
			$pFormData->setStatus(FormPost::MESSAGE_REQUIRED_FIELDS_MISSING);
		}
		else
		{
			$response = false;
			$interessenten = $this->getApplicants($pFormData, $limitResults);

			if (is_array($interessenten) && count($interessenten) > 0)
			{
				$pFormData->setResponseFieldsValues($interessenten);
				$pFormData->setStatus(FormPost::MESSAGE_SUCCESS);
			}
		}
	}


	/**
	 *
	 * @param \onOffice\WPlugin\FormData $pFormData
	 * @param int $limitResults
	 * @return array
	 *
	 */

	private function getApplicants(FormData $pFormData, $limitResults)
	{
		$found = array();
		$searchData = $this->editFormValuesForApiCall($pFormData->getValues());

		$searchFields = array_keys($searchData);
		$searchcrieriaRangeFields = $this->getSearchcriteriaRangeFields();

		$requestParams = array
			(
				'searchdata' => $searchData,
				'outputall' => true,
				'groupbyaddress' => true,
				'limit' => $limitResults,
				'offset' => 0,
			);

		$pSDKWrapper = new SDKWrapper();
		$pSDKWrapper->removeCacheInstances();

		$handle = $pSDKWrapper->addFullRequest(
				onOfficeSDK::ACTION_ID_GET, 'search', 'searchcriteria', $requestParams);
		$pSDKWrapper->sendRequests();

		$response = $pSDKWrapper->getRequestResponse($handle);

		$result = isset($response['data']['records']) &&
				count($response['data']['records']) > 0;

		if ($result)
		{
			$addressIds = array();

			foreach ($response['data']['records'] as $record)
			{
				$addressId = $record['elements']['adresse'];
				$addressIds []= $addressId;
				$elements = $record['elements'];
				$searchParameters = array();

				foreach ($elements as $key => $value)
				{
					if ($this->isSearchcriteriaRangeField($key))
					{
						$origName = $searchcrieriaRangeFields[$key];

						if (in_array($origName, $searchFields))
						{
							if (array_key_exists($origName, $searchParameters))
							{
								continue;
							}

							$vonFieldname = $this->getVonRangeFieldname($origName);
							$bisFieldname = $this->getBisRangeFieldname($origName);

							$vonValue = 0;
							$bisValue = 0;

							if (array_key_exists($vonFieldname, $elements))
							{
								$vonValue = $elements[$vonFieldname];

								if (null == $vonValue)
								{
									$vonValue = 0;
								}
							}

							if (array_key_exists($bisFieldname, $elements))
							{
								$bisValue = $elements[$bisFieldname];

								if (null == $bisValue)
								{
									$bisValue = 0;
								}
							}

							$searchParameters[$origName] = array($vonValue, $bisValue);
						}
					}
					else
					{
						if (in_array($key, $searchFields))
						{
							$searchParameters[$key] = $value;
						}
					}
				}

				$found[$addressId] = $searchParameters;
			}

			if (count($found) > 0)
			{
				$found = $this->setKdNr($found);
			}
		}

		return $found;
	}


	/**
	 *
	 * @param array $applicants
	 * @return array
	 *
	 */

	private function setKdNr($applicants)
	{
		$adressIds = array_keys($applicants);
		$interessenten = array();


		$requestParams = array
			(
				'recordids' => $adressIds,
				'data' => array('KdNr'),
			);

		$pSDKWrapper = new SDKWrapper();
		$pSDKWrapper->removeCacheInstances();

		$handle = $pSDKWrapper->addRequest(
				onOfficeSDK::ACTION_ID_READ, 'address', $requestParams);
		$pSDKWrapper->sendRequests();

		$response = $pSDKWrapper->getRequestResponse($handle);

		$result = isset($response['data']['records']) &&
				count($response['data']['records']) > 0;

		if ($result)
		{
			$records = $response['data']['records'];

			foreach ($records as $record)
			{
				$elements = $record['elements'];
				$interessenten[$elements['KdNr']] = $applicants[$elements['id']];
			}
		}

		return $interessenten;
	}


	/**
	 *
	 * @param array $formValues
	 * @return array
	 *
	 */

	private function editFormValuesForApiCall($formValues)
	{
		$result = array();
		$searchcrieriaRangeFields = $this->getSearchcriteriaRangeFields();

		foreach ($formValues as $name => $value)
		{
			if ($this->isSearchcriteriaRangeField($name))
			{
				$origName = $searchcrieriaRangeFields[$name];

				if (array_key_exists($origName, $result))
				{
					continue;
				}

				$vonFieldname = $this->getVonRangeFieldname($origName);
				$bisFieldname = $this->getBisRangeFieldname($origName);

				$vonValue = 0;
				$bisValue = 0;

				if (array_key_exists($vonFieldname, $formValues))
				{
					$vonValue = $formValues[$vonFieldname];

					if (null == $vonValue)
					{
						$vonValue = 0;
					}
				}

				if (array_key_exists($bisFieldname, $formValues))
				{
					$bisValue = $formValues[$bisFieldname];

					if (null == $bisValue)
					{
						$bisValue = 0;
					}
				}

				if ($vonValue > 0 || $bisValue > 0)
				{
					$result[$origName] = array($vonValue, $bisValue);
				}
			}
			else
			{
				if (null != $value)
				{
					$result[$name] = $value;
				}
			}
		}

		return $result;
	}

	/** @return string */
	static protected function getFormType()
		{ return Form::TYPE_APPLICANT_SEARCH; }
}