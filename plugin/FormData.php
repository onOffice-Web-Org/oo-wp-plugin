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

use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\InputVariableReader;
use onOffice\WPlugin\Controller\InputVariableReaderParser;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\SearchcriteriaFields;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Utility\__String;
use const ONOFFICE_DI_CONFIG_PATH;


/**
 *
 */

class FormData
{
	/** @var int */
	private $_formNo = null;

	/** @var array */
	private $_requiredFields = [];

	/** @var array */
	private $_values = [];

	/** @var string */
	private $_status = null;

	/** @var bool */
	private $_formSent = false;

	/** @var string */
	private $_formtype = null;

	/** @var array */
	private $_responseFieldsValues = [];

	/** @var DataFormConfiguration */
	private $_pDataFormConfiguration = null;

	/**
	 *
	 * @param DataFormConfiguration $pDataFormConfiguration
	 * @param int $formNo
	 *
	 */

	public function __construct(DataFormConfiguration $pDataFormConfiguration, $formNo)
	{
		$this->_pDataFormConfiguration = $pDataFormConfiguration;
		$this->_formNo = $formNo;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getMissingFields(): array
	{
		$missing = [];

		if ($this->_formSent) {
			$filledFormData = array_filter($this->_values, function ($v) {
				return $v !== 0;
			});
			$requiredFields = array_flip($this->_requiredFields);
			$filled = array_intersect_key($filledFormData, $requiredFields);
			$missingKeyValues = array_diff_key($requiredFields, $filled);
			$missing = array_keys($missingKeyValues);
		}

		return $missing;
	}

	/**
	 * @param FieldsCollection $pFieldsCollection
	 * @return array
	 * @throws DependencyException
	 * @throws Field\UnknownFieldException
	 * @throws NotFoundException
	 */
	public function getAddressData(FieldsCollection $pFieldsCollection): array
	{
		$inputs = $this->_pDataFormConfiguration->getInputs();
		$addressData = [];
		$pContainerBuilder = new ContainerBuilder();
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();
		$pSearchcriteriaFields = $pContainer->get(SearchcriteriaFields::class);

		$pInputVariableReaderAddress = new InputVariableReaderParser();

		foreach ($this->_values as $input => $value) {
			$inputConfigName = $pSearchcriteriaFields->getFieldNameOfInput($input);
			$inputModule = $inputs[$inputConfigName] ?? null;

			if (onOfficeSDK::MODULE_ADDRESS === $inputModule) {
				$pField = $pFieldsCollection->getFieldByModuleAndName(onOfficeSDK::MODULE_ADDRESS, $input);

				if ($pField->getType() == FieldTypes::FIELD_TYPE_BOOLEAN) {
					$value = $pInputVariableReaderAddress->parseBool($value);
				}
				$addressData[$input] = $value;
			}
		}
		return $addressData;
	}

	/**
	 *
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function getSearchcriteriaData(): array
	{
		$inputs = $this->_pDataFormConfiguration->getInputs();
		$searchcriteriaData = [];

		$pContainerBuilder = new ContainerBuilder();
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();
		$pSearchcriteriaFields = $pContainer->get(SearchcriteriaFields::class);

		foreach ($this->_values as $input => $value) {
			$inputConfigName = $pSearchcriteriaFields->getFieldNameOfInput($input);
			$inputModule = $inputs[$inputConfigName] ?? null;

			if (onOfficeSDK::MODULE_SEARCHCRITERIA === $inputModule) {
				$searchcriteriaData[$input] = $value;
			}
		}

		return $searchcriteriaData;
	}

	/**
	 * @param FieldsCollection $pFieldsCollection
	 * @return array
	 * @throws DependencyException
	 * @throws Field\UnknownFieldException
	 * @throws NotFoundException
	 */
	public function getFieldLabelsForEmailSubject(FieldsCollection $pFieldsCollection): array
	{
		$inputs = $this->_pDataFormConfiguration->getInputs();
		$output = [];

		$pContainerBuilder = new ContainerBuilder();
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();
		$pSearchcriteriaFields = $pContainer->get(SearchcriteriaFields::class);
		$pInputVariableReaderAddress = $pContainer->get(InputVariableReaderParser::class);

		foreach ($this->_values as $field => $value) {
			$inputConfigName = $pSearchcriteriaFields->getFieldNameOfInput($field);
			$inputModule = $inputs[$inputConfigName] ?? null;
			if ($inputModule === null) {
				continue;
			}
			$pField = $pFieldsCollection->getFieldByModuleAndName($inputModule, $inputConfigName);
			$name = $pField->getName();

			$value = $this->_values[$name] ?? null;
			switch (true) {
				case FieldTypes::isRangeType($pField->getType()):
					if (__String::getNew($field)->endsWith(SearchcriteriaFields::RANGE_FROM)) {
						$output[$name . SearchcriteriaFields::RANGE_FROM] = $this->_values[$name . SearchcriteriaFields::RANGE_FROM];
					} elseif (__String::getNew($field)->endsWith(SearchcriteriaFields::RANGE_UPTO)) {
						$output[$name . SearchcriteriaFields::RANGE_UPTO] = $this->_values[$name . SearchcriteriaFields::RANGE_UPTO];
					} else {
						$output[$name] = $this->_values[$name];
					}
					break;

				case FieldTypes::isMultipleSelectType($pField->getType()):
					if (is_array($value)) {
						$tmpOutput = [];
						foreach ($value as $val) {
							$tmpOutput[] = (array_key_exists($val, $pField->getPermittedvalues()) ? $pField->getPermittedvalues()[$val] : $val);
						}
						$output[$name] = implode(', ', $tmpOutput);
					} else {
						$output[$name] = (array_key_exists($value, $pField->getPermittedvalues()) ? $pField->getPermittedvalues()[$value] : $value);
					}
					break;

				case FieldTypes::FIELD_TYPE_BOOLEAN === $pField->getType():
					$output[$name] = $pInputVariableReaderAddress->parseBool($value);
					break;

				default:
					$output[$name] = $value;
					break;
			}
		}

		return $output;
	}

	/** @param string[] $requiredFields */
	public function setRequiredFields(array $requiredFields)
		{ $this->_requiredFields = $requiredFields; }

	/** @return array */
	public function getRequiredFields(): array
		{ return $this->_requiredFields; }

	/** @param array $values */
	public function setValues(array $values)
		{ $this->_values = $values; }

	/** @return array */
	public function getValues(): array
		{ return $this->_values; }

	/** @param string $status */
	public function setStatus($status)
		{ $this->_status = $status; }

	/** @return string */
	public function getStatus()
		{ return $this->_status; }

	/**	@param bool $formSent Whether the Form was sent using GET or POST yet */
	public function setFormSent(bool $formSent)
		{ $this->_formSent = $formSent; }

	/** @return bool */
	public function getFormSent(): bool
		{ return $this->_formSent; }

	/** @param string $formtype */
	public function setFormtype(string $formtype)
		{ $this->_formtype = $formtype; }

	/** @return string */
	public function getFormtype()
		{ return $this->_formtype; }

	/** @param array $values */
	public function setResponseFieldsValues(array $values)
		{ $this->_responseFieldsValues = $values; }

	/** @return array */
	public function getResponseFieldsValues(): array
		{ return $this->_responseFieldsValues; }

	/** @return DataFormConfiguration */
	public function getDataFormConfiguration(): DataFormConfiguration
		{ return $this->_pDataFormConfiguration; }

	/** @return int */
	public function getFormNo()
		{ return $this->_formNo; }
}
