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

namespace onOffice\WPlugin;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationApplicantSearch;
use onOffice\WPlugin\Form\FormPostApplicantSearchConfiguration;
use onOffice\WPlugin\Form\FormPostApplicantSearchConfigurationDefault;
use onOffice\WPlugin\Form\FormPostConfiguration;
use onOffice\WPlugin\FormData;
use onOffice\WPlugin\FormPost;


/**
 *
 * Applicant search form
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2016, onOffice(R) Software AG
 *
 */

class FormPostApplicantSearch
	extends FormPost
{
	/** */
	const LIMIT_RESULTS = 100;

	/** @var FormPostApplicantSearchConfiguration */
	private $_pFormPostApplicantSearchConfiguration = null;


	/**
	 *
	 * @param FormPostConfiguration $pFormPostConfiguration
	 * @param FormPostApplicantSearchConfiguration $pFormPostApplicantSearchConfiguration
	 *
	 */

	public function __construct(FormPostConfiguration $pFormPostConfiguration = null,
		FormPostApplicantSearchConfiguration $pFormPostApplicantSearchConfiguration = null)
	{
		$this->_pFormPostApplicantSearchConfiguration = $pFormPostApplicantSearchConfiguration ??
			new FormPostApplicantSearchConfigurationDefault();

		parent::__construct($pFormPostConfiguration);
	}


	/**
	 *
	 * @param FormData $pFormData
	 *
	 */

	protected function analyseFormContentByPrefix(FormData $pFormData)
	{
		/* @var $pFormConfig DataFormConfigurationApplicantSearch */
		$pFormConfig = $pFormData->getDataFormConfiguration();
		$limitResults = $pFormConfig->getLimitResults();

		if ($limitResults <= 0) {
			$limitResults = self::LIMIT_RESULTS;
		}

		if ($pFormData->getMissingFields() !== []) {
			$pFormData->setStatus(FormPost::MESSAGE_REQUIRED_FIELDS_MISSING);
		} else {
			$applicants = $this->getApplicants($pFormData, $limitResults);

			if (is_array($applicants)) {
				$pFormData->setResponseFieldsValues($applicants);
				$pFormData->setStatus(FormPost::MESSAGE_SUCCESS);
			}
		}
	}


	/**
	 *
	 * @param DataFormConfiguration $pFormConfig
	 * @return array
	 *
	 */

	protected function getAllowedPostVars(DataFormConfiguration $pFormConfig): array
	{
		$formFields = parent::getAllowedPostVars($pFormConfig);
		return $this->getFormFieldsConsiderSearchcriteria($formFields, false);
	}


	/**
	 *
	 * @param FormData $pFormData
	 * @param int $limitResults
	 * @return array
	 *
	 */

	private function getApplicants(FormData $pFormData, $limitResults): array
	{
		$found = [];
		$searchData = $this->editFormValuesForApiCall($pFormData->getValues());
		$searchFields = array_keys($searchData);
		$searchcrieriaRangeFields = $this->getSearchcriteriaRangeFields();

		$requestParams = [
			'searchdata' => $searchData,
			'outputall' => true,
			'groupbyaddress' => true,
			'limit' => $limitResults,
			'offset' => 0,
		];

		$pSDKWrapper = $this->_pFormPostApplicantSearchConfiguration->getSDKWrapper();
		$pApiClientAction = new APIClientActionGeneric($pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'search');
		$pApiClientAction->setResourceId('searchcriteria');
		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->addRequestToQueue();
		$pSDKWrapper->sendRequests();

		if (!$pApiClientAction->getResultStatus()) {
			// Exception?
			return [];
		}

		$response = $pApiClientAction->getResultRecords();

		foreach ($response as $record) {
			$addressId = $record['elements']['adresse'];
			$elements = $record['elements'];
			$searchParameters = [];

			foreach ($elements as $key => $value) {
				if ($this->isSearchcriteriaRangeField($key)) {
					$origName = $searchcrieriaRangeFields[$key];

					if (in_array($origName, $searchFields)) {
						if (array_key_exists($origName, $searchParameters)) {
							continue;
						}

						$vonFieldname = $this->getVonRangeFieldname($origName);
						$bisFieldname = $this->getBisRangeFieldname($origName);

						$vonValue = 0;
						$bisValue = 0;

						if (array_key_exists($vonFieldname, $elements)) {
							$vonValue = $elements[$vonFieldname];

							if (null == $vonValue) {
								$vonValue = 0;
							}
						}

						if (array_key_exists($bisFieldname, $elements)) {
							$bisValue = $elements[$bisFieldname];

							if (null == $bisValue) {
								$bisValue = 0;
							}
						}
						$searchParameters[$origName] = [$vonValue, $bisValue];
					}
				} elseif (in_array($key, $searchFields)) {
					$searchParameters[$key] = $value;
				}
			}
			$found[$addressId] = $searchParameters;
		}

		if ($found !== []) {
			$found = $this->setKdNr($found);
		}

		return $found;
	}


	/**
	 *
	 * @param array $applicants
	 * @return array
	 *
	 */

	private function setKdNr(array $applicants): array
	{
		$adressIds = array_keys($applicants);
		$pSDKWrapper = $this->_pFormPostApplicantSearchConfiguration->getSDKWrapper();

		$pApiClientAction = new APIClientActionGeneric($pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'address');
		$pApiClientAction->setParameters([
			'recordids' => $adressIds,
			'data' => ['KdNr'],
		]);
		$pApiClientAction->addRequestToQueue();
		$pSDKWrapper->sendRequests();

		$results = [];
		if ($pApiClientAction->getResultStatus()) {
			$records = $pApiClientAction->getResultRecords();

			foreach ($records as $record) {
				$elements = $record['elements'];
				$results[$elements['KdNr']] = $applicants[$elements['id']];
			}
		}

		return $results;
	}


	/**
	 *
	 * @param array $formValues
	 * @return array
	 *
	 */

	private function editFormValuesForApiCall($formValues)
	{
		$result = [];
		$searchcrieriaRangeFields = $this->getSearchcriteriaRangeFields();

		foreach ($formValues as $name => $value) {
			if ($this->isSearchcriteriaRangeField($name)) {
				$origName = $searchcrieriaRangeFields[$name];

				if (isset($result[$origName])) {
					continue;
				}

				$vonFieldname = $this->getVonRangeFieldname($origName);
				$bisFieldname = $this->getBisRangeFieldname($origName);

				$vonValue = 0;
				$bisValue = 0;

				if (array_key_exists($vonFieldname, $formValues)) {
					$vonValue = $formValues[$vonFieldname];

					if (null == $vonValue) {
						$vonValue = 0;
					}
				}

				if (array_key_exists($bisFieldname, $formValues)) {
					$bisValue = $formValues[$bisFieldname];

					if (null == $bisValue) {
						$bisValue = 0;
					}
				}

				if ($vonValue > 0 || $bisValue > 0) {
					$result[$origName] = [$vonValue, $bisValue];
				}
			} else {
				if (null != $value) {
					$result[$name] = $value;
				}
			}
		}

		return $result;
	}
}