<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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
use onOffice\WPlugin\Field\DistinctFieldsFilter;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use WP_UnitTestCase;


/**
 *
 */

class TestClassDistinctFieldsFilter
	extends WP_UnitTestCase
{
	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort = null;



	/**
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pFieldsCollectionBuilderShort = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
			->setMethods(['addFieldsAddressEstate', 'addFieldsSearchCriteria'])
			->setConstructorArgs([new Container])
			->getMock();

		$this->_pFieldsCollectionBuilderShort->method('addFieldsAddressEstate')
			->with($this->anything())
			->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
				$pField1 = new Field('objektart', onOfficeSDK::MODULE_ESTATE);
				$pField1->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pField1);

				$pField2 = new Field('wohnflaeche', onOfficeSDK::MODULE_ESTATE);
				$pField2->setType(FieldTypes::FIELD_TYPE_FLOAT);
				$pFieldsCollection->addField($pField2);

				$pFieldKaufpreis = new Field('kaufpreis', onOfficeSDK::MODULE_ESTATE);
				$pFieldKaufpreis->setType(FieldTypes::FIELD_TYPE_FLOAT);
				$pFieldsCollection->addField($pFieldKaufpreis);

				$pField3 = new Field('ort', onOfficeSDK::MODULE_ESTATE);
				$pField3->setType(FieldTypes::FIELD_TYPE_VARCHAR);
				$pFieldsCollection->addField($pField3);

				return $this->_pFieldsCollectionBuilderShort;
			}));

			$this->_pFieldsCollectionBuilderShort->method('addFieldsSearchCriteria')
			->with($this->anything())
			->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
				$pField1 = new Field('objektart', onOfficeSDK::MODULE_SEARCHCRITERIA);
				$pField1->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pField1);

				$pField2 = new Field('wohnflaeche', onOfficeSDK::MODULE_SEARCHCRITERIA);
				$pField2->setType(FieldTypes::FIELD_TYPE_FLOAT);
				$pFieldsCollection->addField($pField2);

				$pField3 = new Field('boden', onOfficeSDK::MODULE_SEARCHCRITERIA);
				$pField3->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pField3);

				return $this->_pFieldsCollectionBuilderShort;
			}));
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\DistinctFieldsFilter::filter
	 * @covers onOffice\WPlugin\Field\DistinctFieldsFilter::isMultiselectableType
	 * @covers onOffice\WPlugin\Field\DistinctFieldsFilter::isDistinctField
	 * @covers onOffice\WPlugin\Field\DistinctFieldsFilter::createDefaultFilter
	 * @covers onOffice\WPlugin\Field\DistinctFieldsFilter::filterForEstateVon
	 * @covers onOffice\WPlugin\Field\DistinctFieldsFilter::filterForEstateBis
	 *
	 */

	public function testFilterEstates()
	{
		$inputValues = [
			'' => 'Send',
			'objektart' => ['haus'],
			'wohnflaeche__von' => '100',
			'wohnflaeche__bis' => '300',
			'kaufpreis__bis' => '300000',
			'kaufpreis__von' => '100000',
			'ort' => 'Aachen',
		];

		$expectedResult = [
			'objektart' => [['op' => 'in', 'val' => ['haus']]],
			'wohnflaeche' => [['op' => 'between', 'val' => ['100', '300']]],
			'kaufpreis' => [['op' => 'between', 'val' => ['100000', '300000']]],
			'ort' => [['op' => '=', 'val' => 'Aachen']]
		];

		$pInstance = new DistinctFieldsFilter($this->_pFieldsCollectionBuilderShort);
		$this->assertEquals($expectedResult, $pInstance->filter('objekttyp', $inputValues, 'estate', ['objekttyp', 'objektart','wohnflaeche__von', 'wohnflaeche__bis','kaufpreis__von', 'kaufpreis__bis','ort']));
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\DistinctFieldsFilter::filter
	 * @covers onOffice\WPlugin\Field\DistinctFieldsFilter::isMultiselectableType
	 *
	 */

	public function testFilterSearchcriteria()
	{
		$expectedResult = [
			'objektart' => [
				['op' => 'in', 'val' => 'haus']
			],
			'wohnflaeche__von' => [['op' => '<=', 'val' => 100]],
			'wohnflaeche__bis' => [['op' => '>=', 'val' => 100]],
			'boden' => [['op' => 'in', 'val' => ['marmor']]],
		];

		$inputValues = [
			'oo_formid' => 'applicant-search-form-1',
			'oo_formno' => '1',
			'objektart' => 'haus',
			'objekttyp' => '',
			'vermarktungsart' => '',
			'wohnflaeche' => '100',
			'boden' => ['marmor'],
			'range_land' => '',
			'range_plz' => '',
			'range_strasse' => '',
			'' => 'Search+for+Prospective+Buyers',
		];

		$pInstance = new DistinctFieldsFilter($this->_pFieldsCollectionBuilderShort);
		$this->assertEquals($expectedResult, $pInstance->filter('objekttyp', $inputValues, 'searchcriteria', ['objektart', 'boden', 'wohnflaeche']));
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\DistinctFieldsFilter::__construct
	 *
	 */
	public function testConstruct()
	{
		$pInstance = new DistinctFieldsFilter($this->_pFieldsCollectionBuilderShort);
		$this->assertInstanceOf(DistinctFieldsFilter::class, $pInstance);
	}
}