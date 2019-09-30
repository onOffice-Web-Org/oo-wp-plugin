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
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\SearchcriteriaFields;
use onOffice\WPlugin\Form\FormPostConfiguration;
use onOffice\WPlugin\FormData;
use onOffice\WPlugin\FormPost;
use onOffice\WPlugin\Utility\__String;


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

	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var SearchcriteriaFields */
	private $_pSearchcriteriaFields = null;


	/**
	 *
	 * @param FormPostConfiguration $pFormPostConfiguration
	 * @param SDKWrapper $pSDKWrapper
	 * @param SearchcriteriaFields $pSearchcriteriaFields
	 * @param FieldsCollectionBuilderShort $pBuilderShort
	 *
	 */

	public function __construct(
		FormPostConfiguration $pFormPostConfiguration,
		SDKWrapper $pSDKWrapper,
		SearchcriteriaFields $pSearchcriteriaFields,
		FieldsCollectionBuilderShort $pBuilderShort)
	{
		$this->_pSDKWrapper = $pSDKWrapper;
		$this->_pSearchcriteriaFields = $pSearchcriteriaFields;

		parent::__construct($pFormPostConfiguration, $pBuilderShort, $pSearchcriteriaFields);
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

		$applicants = $this->getApplicants($pFormData, $limitResults);
		$pFormData->setResponseFieldsValues($applicants);
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
		return $this->_pSearchcriteriaFields->getFormFields($formFields);
	}


	/**
	 *
	 * @param FormData $pFormData
	 * @param int $limitResults
	 * @return array
	 *
	 */

	private function getApplicants(FormData $pFormData, int $limitResults): array
	{
		$requestParams = [
			'searchdata' => $pFormData->getValues(),
			'outputall' => true,
			'groupbyaddress' => true,
			'limit' => $limitResults,
			'offset' => 0,
		];

		$pApiClientAction = new APIClientActionGeneric($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'search');
		$pApiClientAction->setResourceId('searchcriteria');
		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->addRequestToQueue()->sendRequests();

		$resultMeta = $pApiClientAction->getResultMeta();
		$this->setAbsolutCountResults($resultMeta['cntabsolute']);

		$response = $pApiClientAction->getResultRecords();

		if ($response === []) {
			return [];
		}

		$viewFields = $pFormData->getDataFormConfiguration()->getInputs();
		$foundApplicants = [];

		foreach ($response as $record) {
			$addressId = $record['elements']['adresse'];
			$foundApplicants[$addressId] = $this->collectSearchParametersForRecord($record, $viewFields);
		}

		return $this->setKdNr($foundApplicants);
	}


	/**
	 *
	 * @param array $record
	 * @param array $viewFields
	 * @return array
	 *
	 */

	private function collectSearchParametersForRecord(array $record, array $viewFields): array
	{
		$elements = $record['elements'];
		$searchParameters = [];

		foreach ($elements as $key => $value) {
			$origName = $this->getOriginalFieldNameByRangeField($key);
			if (!isset($viewFields[$origName])) {
				continue;
			}

			if ($this->isSearchcriteriaRangeField($key)) {
				$vonValue = $elements[$origName.SearchcriteriaFields::RANGE_FROM] ?? 0;
				$bisValue = $elements[$origName.SearchcriteriaFields::RANGE_UPTO] ?? 0;

				$searchParameters[$origName] = [$vonValue, $bisValue];
			} else {
				$searchParameters[$origName] = $value;
			}
		}

		return $searchParameters;
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
		$pApiClientAction = new APIClientActionGeneric
			($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'address');
		$pApiClientAction->setParameters([
			'recordids' => $adressIds,
			'data' => ['KdNr'],
		]);

		$pApiClientAction->addRequestToQueue()->sendRequests();
		$records = $pApiClientAction->getResultRecords();
		$results = [];

		foreach ($records as $record) {
			$elements = $record['elements'];
			$results[$elements['KdNr']] = $applicants[$elements['id']];
		}

		return $results;
	}


	/**
	 *
	 * @param string $rangeField
	 * @return bool
	 *
	 */

	private function isSearchcriteriaRangeField(string $rangeField): bool
	{
		$pString = __String::getNew($rangeField);
		return $pString->endsWith('__von') || $pString->endsWith('__bis');
	}


	/**
	 *
	 * @param string $rangeField
	 * @return string
	 *
	 */

	private function getOriginalFieldNameByRangeField(string $rangeField): string
	{
		if (!$this->isSearchcriteriaRangeField($rangeField)) {
			return $rangeField;
		}
		$pString = __String::getNew($rangeField);
		return $pString->sub(0, -5);
	}
}