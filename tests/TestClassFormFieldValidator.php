<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

declare (strict_types=1);

namespace onOffice\tests;

use DI\Container;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\SearchcriteriaFields;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Form\FormFieldValidator;
use onOffice\WPlugin\RequestVariablesSanitizer;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use WP_UnitTestCase;

/**
 *
 * test class for FormFieldValidator
 *
 */

class TestClassFormFieldValidator
	extends WP_UnitTestCase
{
	/** @var FormFieldValidator */
	private $_pInstance = null;

	/** FieldCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort = null;



	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pFieldsCollectionBuilderShort = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
			->setMethods(['addFieldsAddressEstate', 'addFieldsSearchCriteria', 'addFieldsFormFrontend'])
			->setConstructorArgs([new Container])
			->getMock();

		$this->_pFieldsCollectionBuilderShort->method('addFieldsAddressEstate')
			->with($this->anything())
			->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
				$pFieldVorname = new Field('Vorname', onOfficeSDK::MODULE_ADDRESS);
				$pFieldVorname->setType(FieldTypes::FIELD_TYPE_VARCHAR);
				$pFieldsCollection->addField($pFieldVorname);

				$pFieldAnzZimmer = new Field('anzahl_zimmer', onOfficeSDK::MODULE_ESTATE);
				$pFieldAnzZimmer->setType(FieldTypes::FIELD_TYPE_INTEGER);
				$pFieldsCollection->addField($pFieldAnzZimmer);

				$pFieldWohnfl = new Field('wohnflaeche', onOfficeSDK::MODULE_ESTATE);
				$pFieldWohnfl->setType(FieldTypes::FIELD_TYPE_FLOAT);
				$pFieldsCollection->addField($pFieldWohnfl);

				$pFieldBad = new Field('bad', onOfficeSDK::MODULE_ESTATE);
				$pFieldBad->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldBad->setPermittedvalues(['wanne' => 'Wanne', 'fenster' => 'Fenster', 'bidet' => 'Bidet']);
				$pFieldsCollection->addField($pFieldBad);

				$pFieldAnzBadezi = new Field('anzahl_badezimmer', onOfficeSDK::MODULE_ESTATE);
				$pFieldAnzBadezi->setType(FieldTypes::FIELD_TYPE_FLOAT);
				$pFieldsCollection->addField($pFieldAnzBadezi);

				return $this->_pFieldsCollectionBuilderShort;
			}));

		$this->_pFieldsCollectionBuilderShort->method('addFieldsSearchCriteria')
			->with($this->anything())
			->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
				$pField1 = new Field('objekttyp', onOfficeSDK::MODULE_SEARCHCRITERIA);
				$pField1->setPermittedvalues(['reihenendhaus' => 'Reihenendhaus', 'stadthaus' => 'Stadthaus']);
				$pField1->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pField1);

				$pFieldRegZusatz = new Field('regionaler_zusatz', onOfficeSDK::MODULE_SEARCHCRITERIA);
				$pFieldRegZusatz->setType('displayAll');
				$pFieldsCollection->addField($pFieldRegZusatz);

				return $this->_pFieldsCollectionBuilderShort;
			}));

			$this->_pFieldsCollectionBuilderShort->method('addFieldsFormFrontend')
					->with($this->anything())
					->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
				$pField1 = new Field('region_plz', onOfficeSDK::MODULE_SEARCHCRITERIA);
				$pField1->setType(FieldTypes::FIELD_TYPE_VARCHAR);
				$pFieldsCollection->addField($pField1);

				return $this->_pFieldsCollectionBuilderShort;
			}));

			$pSeachrcriteriaFields = new SearchcriteriaFields($this->_pFieldsCollectionBuilderShort);

			$this->_pInstance = new FormFieldValidator(new RequestVariablesSanitizer, $pSeachrcriteriaFields);
	}

	/**
	 *
	 * @covers onOffice\WPlugin\Form\FormFieldValidator::getValidatedValues
	 * @covers onOffice\WPlugin\Form\FormFieldValidator::isEmptyValue
	 * @covers onOffice\WPlugin\Form\FormFieldValidator::getValueFromRequest
	 * @covers onOffice\WPlugin\Form\FormFieldValidator::isMultipleSingleSelectAllowed
	 *
	 * @throws UnknownFieldException
	 */

	public function testGetValidatedValues()
	{
		$_POST = [
			'Vorname' => 'Max',
			'anzahl_zimmer' => '5.9',
			'wohnflaeche' => '105.4',
			'anzahl_badezimmer' => '',
			'bad' => ['wanne', 'fenster'],
			'objekttyp' => ['reihenendhaus','stadthaus'],
			'regionaler_zusatz' => ['regZusatz1', 'regZusatz2'],
		];

		$data = [
			'Vorname' => 'address',
			'anzahl_zimmer' => 'estate',
			'wohnflaeche' => 'estate',
			'anzahl_badezimmer' => 'estate',
			'bad' => 'estate',
			'objekttyp' => 'searchcriteria',
			'regionaler_zusatz' => 'searchcriteria',
		];

		$expectedData = [
			'Vorname' => 'Max',
			'anzahl_zimmer' => 5,
			'wohnflaeche' => 105.4,
			'bad' => ['wanne', 'fenster'],
			'objekttyp' => ['reihenendhaus','stadthaus'],
			'regionaler_zusatz' => ['regZusatz1', 'regZusatz2'],
		];

		$pFieldsCollection = $this->buildFieldsCollection();
		$this->assertEquals($expectedData, $this->_pInstance->getValidatedValues($data, $pFieldsCollection));
	}

	/**
	 * @return FieldsCollection
	 */
	private function buildFieldsCollection(): FieldsCollection
	{
		$pFieldsCollection = new FieldsCollection;
		$pFieldVorname = new Field('Vorname', onOfficeSDK::MODULE_ADDRESS);
		$pFieldVorname->setType(FieldTypes::FIELD_TYPE_VARCHAR);
		$pFieldsCollection->addField($pFieldVorname);

		$pFieldAnzZimmer = new Field('anzahl_zimmer', onOfficeSDK::MODULE_ESTATE);
		$pFieldAnzZimmer->setType(FieldTypes::FIELD_TYPE_INTEGER);
		$pFieldsCollection->addField($pFieldAnzZimmer);

		$pFieldWohnfl = new Field('wohnflaeche', onOfficeSDK::MODULE_ESTATE);
		$pFieldWohnfl->setType(FieldTypes::FIELD_TYPE_FLOAT);
		$pFieldsCollection->addField($pFieldWohnfl);

		$pFieldBad = new Field('bad', onOfficeSDK::MODULE_ESTATE);
		$pFieldBad->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
		$pFieldBad->setPermittedvalues(['wanne' => 'Wanne', 'fenster' => 'Fenster', 'bidet' => 'Bidet']);
		$pFieldsCollection->addField($pFieldBad);

		$pFieldAnzBadezi = new Field('anzahl_badezimmer', onOfficeSDK::MODULE_ESTATE);
		$pFieldAnzBadezi->setType(FieldTypes::FIELD_TYPE_FLOAT);
		$pFieldsCollection->addField($pFieldAnzBadezi);

		$pField1 = new Field('objekttyp', onOfficeSDK::MODULE_SEARCHCRITERIA);
		$pField1->setPermittedvalues(['reihenendhaus' => 'Reihenendhaus', 'stadthaus' => 'Stadthaus']);
		$pField1->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
		$pFieldsCollection->addField($pField1);

		$pFieldRegZusatz = new Field('regionaler_zusatz', onOfficeSDK::MODULE_SEARCHCRITERIA);
		$pFieldRegZusatz->setType('displayAll');
		$pFieldsCollection->addField($pFieldRegZusatz);
		return $pFieldsCollection;
	}
}