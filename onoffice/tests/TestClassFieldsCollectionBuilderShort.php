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
use Generator;
use onOffice\WPlugin\Field\Collection\FieldLoaderGeneric;
use onOffice\WPlugin\Field\Collection\FieldLoaderSearchCriteria;
use onOffice\WPlugin\Field\Collection\FieldRowConverterSearchCriteria;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Types\FieldsCollection;
use WP_UnitTestCase;


/**
 *
 */

class TestClassFieldsCollectionBuilderShort
	extends WP_UnitTestCase
{
	/** @var FieldsCollectionBuilderShort */
	private $_pSubject = null;

	/** @var array */
	private $_exampleRowsByModule = [
		'address' => [
			'testAddressField1' => [
				'label' => 'testField Label',
				'module' => 'address',
				'content' => 'Stammdaten',
				'permittedvalues' => [],
				'type' => 'varchar',
				'rangefield' => false,
			],
		],
		'estate' => [
			'testEstateField2' => [
				'label' => 'testField2 Label',
				'module' => 'estate',
				'content' => 'Technische-Angaben',
				'permittedvalues' => [],
				'type' => 'varchar',
				'rangefield' => false,
			],
		],
		'searchcriteria' => [
			'testSKField1' => [
				'label' => 'testField2 Label',
				'module' => 'searchcriteria',
				'content' => 'Technische-Angaben',
				'permittedvalues' => [],
				'type' => 'varchar',
				'rangefield' => false,
			],
		],
	];


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$pContainer = new Container();
		$pFieldLoaderGeneric = $this->getMockBuilder(FieldLoaderGeneric::class)
			->setConstructorArgs([new SDKWrapperMocker()])
			->setMethods(['load'])
			->getMock();
		$pFieldLoaderGeneric->method('load')->will($this->returnCallback(function(): Generator {
			yield from $this->_exampleRowsByModule['address'] + $this->_exampleRowsByModule['estate'];
		}));

		$pFieldLoaderSearchCriteria = $this->getMockBuilder(FieldLoaderSearchCriteria::class)
			->setConstructorArgs([new SDKWrapperMocker(), new FieldRowConverterSearchCriteria()])
			->setMethods(['load'])
			->getMock();
		$pFieldLoaderSearchCriteria->method('load')->will($this->returnCallback(function(): Generator {
			yield from $this->_exampleRowsByModule['searchcriteria'];
		}));

		$pContainer->set(FieldLoaderGeneric::class, $pFieldLoaderGeneric);
		$pContainer->set(FieldLoaderSearchCriteria::class, $pFieldLoaderSearchCriteria);
		$this->_pSubject = new FieldsCollectionBuilderShort($pContainer);
	}


	/**
	 *
	 */

	public function testAddFieldsAddressEstate()
	{
		$pFieldsCollection = new FieldsCollection();
		$this->assertEmpty($pFieldsCollection->getAllFields());
		$this->assertSame($this->_pSubject,
			$this->_pSubject->addFieldsAddressEstate($pFieldsCollection));
		$this->assertCount(2, $pFieldsCollection->getAllFields());
	}


	/**
	 *
	 */

	public function testAddFieldsSearchCriteria()
	{
		$pFieldsCollection = new FieldsCollection();
		$this->assertSame($this->_pSubject, $this->_pSubject->addFieldsSearchCriteria($pFieldsCollection));
		$this->assertCount(1, $pFieldsCollection->getAllFields());
	}


	/**
	 *
	 */

	public function testAddFieldsFormBackend()
	{
		$pFieldsCollection = new FieldsCollection();
		$this->assertSame($this->_pSubject, $this->_pSubject->addFieldsFormBackend($pFieldsCollection));
		$this->assertCount(4, $pFieldsCollection->getAllFields());
	}


	/**
	 *
	 */

	public function testAddFieldsFormFrontend()
	{
		$pFieldsCollection = new FieldsCollection();
		$this->assertSame($this->_pSubject, $this->_pSubject->addFieldsFormFrontend($pFieldsCollection));
		$this->assertCount(13, $pFieldsCollection->getAllFields());
	}


	/**
	 *
	 */

	public function testAddFieldsSearchCriteriaSpecificBackend()
	{
		$pFieldsCollection = new FieldsCollection();
		$this->assertSame($this->_pSubject, $this->_pSubject->addFieldsSearchCriteriaSpecificBackend
			($pFieldsCollection));
		$this->assertCount(2, $pFieldsCollection->getAllFields());
	}


	/**
	 *
	 * @coversNothing
	 *
	 */

	public function testCombination()
	{
		$pFieldsCollection = new FieldsCollection();
		$this->_pSubject
			->addFieldsAddressEstate($pFieldsCollection)
			->addFieldsFormBackend($pFieldsCollection)
			->addFieldsFormFrontend($pFieldsCollection)
			->addFieldsSearchCriteria($pFieldsCollection)
			->addFieldsSearchCriteriaSpecificBackend($pFieldsCollection);
		$this->assertCount(22, $pFieldsCollection->getAllFields());
	}
}
