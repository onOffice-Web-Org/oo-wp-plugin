<?php

/**
 *
 *    Copyright (C) 2025 onOffice GmbH
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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\Collection\FieldsCollectionCountryValuesForSearchCriteria;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use WP_UnitTestCase;

/**
 * @covers onOffice\WPlugin\Field\Collection\FieldsCollectionCountryValuesForSearchCriteria
 */
class TestClassFieldsCollectionCountryValuesForSearchCriteria
	extends WP_UnitTestCase
{
	/** @var array */
	private $_countries = [
		'DEU' => 'Deutschland',
		'AUT' => 'Österreich',
	];

	/**
	 * @param array $permittedValuesRangeCountry
	 * @return FieldsCollection
	 */
	private function buildFieldsCollection(array $permittedValuesRangeCountry): FieldsCollection
	{
		$pFieldsCollection = new FieldsCollection;

		$pFieldEstateCountry = new Field('land', onOfficeSDK::MODULE_ESTATE);
		$pFieldEstateCountry->setType(FieldTypes::FIELD_TYPE_SINGLESELECT);
		$pFieldEstateCountry->setPermittedvalues($this->_countries);
		$pFieldsCollection->addField($pFieldEstateCountry);

		$pFieldRangeCountry = new Field('range_land', onOfficeSDK::MODULE_SEARCHCRITERIA);
		$pFieldRangeCountry->setType(FieldTypes::FIELD_TYPE_SINGLESELECT);
		$pFieldRangeCountry->setPermittedvalues($permittedValuesRangeCountry);
		$pFieldsCollection->addField($pFieldRangeCountry);

		return $pFieldsCollection;
	}

	/**
	 * The API delivers range_land without permitted values, which left the country
	 * dropdown of the radius search without a single option.
	 *
	 * @throws UnknownFieldException
	 */
	public function testAddCountryValuesFillsEmptyRangeCountry()
	{
		$pFieldsCollection = $this->buildFieldsCollection([]);
		(new FieldsCollectionCountryValuesForSearchCriteria)->addCountryValues($pFieldsCollection);

		$pFieldRangeCountry = $pFieldsCollection->getFieldByModuleAndName
			(onOfficeSDK::MODULE_SEARCHCRITERIA, 'range_land');
		$this->assertSame($this->_countries, $pFieldRangeCountry->getPermittedvalues());
	}

	/**
	 * @throws UnknownFieldException
	 */
	public function testAddCountryValuesKeepsValuesDeliveredByTheApi()
	{
		$apiValues = ['CHE' => 'Schweiz'];
		$pFieldsCollection = $this->buildFieldsCollection($apiValues);
		(new FieldsCollectionCountryValuesForSearchCriteria)->addCountryValues($pFieldsCollection);

		$pFieldRangeCountry = $pFieldsCollection->getFieldByModuleAndName
			(onOfficeSDK::MODULE_SEARCHCRITERIA, 'range_land');
		$this->assertSame($apiValues, $pFieldRangeCountry->getPermittedvalues());
	}

	/**
	 * Forms without a radius search and collections built before the estate fields are
	 * loaded must not blow up.
	 */
	public function testAddCountryValuesWithoutFields()
	{
		$pFieldsCollection = new FieldsCollection;
		(new FieldsCollectionCountryValuesForSearchCriteria)->addCountryValues($pFieldsCollection);
		$this->assertCount(0, $pFieldsCollection->getAllFields());
	}

	/**
	 * @throws UnknownFieldException
	 */
	public function testAddCountryValuesWithoutEstateCountryField()
	{
		$pFieldsCollection = new FieldsCollection;
		$pFieldRangeCountry = new Field('range_land', onOfficeSDK::MODULE_SEARCHCRITERIA);
		$pFieldRangeCountry->setType(FieldTypes::FIELD_TYPE_SINGLESELECT);
		$pFieldsCollection->addField($pFieldRangeCountry);

		(new FieldsCollectionCountryValuesForSearchCriteria)->addCountryValues($pFieldsCollection);

		$this->assertSame([], $pFieldsCollection->getFieldByModuleAndName
			(onOfficeSDK::MODULE_SEARCHCRITERIA, 'range_land')->getPermittedvalues());
	}
}
