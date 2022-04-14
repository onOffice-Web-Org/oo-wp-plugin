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

use Generator;
use onOffice\WPlugin\Field\Collection\FieldLoader;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilder;
use WP_UnitTestCase;


/**
 *
 */

class TestClassFieldsCollectionBuilder
	extends WP_UnitTestCase
{
	/** @var FieldsCollectionBuilder */
	private $_pSubject = null;

	/** @var array */
	private $_exampleRows = [
		'testField1' => [
			'label' => 'testField Label',
			'module' => 'address',
			'content' => 'Stammdaten',
			'permittedvalues' => [],
			'type' => 'varchar',
			'rangefield' => false,
		],
		'testField2' => [
			'label' => 'testField2 Label',
			'module' => 'estate',
			'content' => 'Technische-Angaben',
			'permittedvalues' => [],
			'type' => 'varchar',
			'rangefield' => false,
		],
	];


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pSubject = new FieldsCollectionBuilder();
	}


	/**
	 *
	 */

	public function testBuildFieldsCollection()
	{
		$pFieldLoader = $this->getMockBuilder(FieldLoader::class)
			->setMethods(['load'])
			->getMock();
		$pFieldLoader->method('load')
			->will($this->returnCallback(function(): Generator {
				yield from $this->_exampleRows;
			}));

		$pFieldsCollection = $this->_pSubject->buildFieldsCollection($pFieldLoader);

		$this->assertCount(2, $pFieldsCollection->getAllFields());

		foreach ($pFieldsCollection->getAllFields() as $pField) {
			$this->assertAssocArraySubset($this->_exampleRows[$pField->getName()], $pField->getAsRow());
		}
	}
}