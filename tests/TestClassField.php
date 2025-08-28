<?php

/**
 *
 *    Copyright (C) 2018-2019 onOffice GmbH
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

use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldTypes;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassField
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testDefaults()
	{
		$pField = new Field('testField123', 'testModule');
		$this->assertEquals('testField123', $pField->getName());
		$this->assertEquals('testModule', $pField->getModule());
		$this->assertEquals('', $pField->getCategory());
		$this->assertNull($pField->getDefault());
		$this->assertEquals('', $pField->getLabel());
		$this->assertEquals(0, $pField->getLength());
		$this->assertEmpty($pField->getPermittedvalues());
		$this->assertEmpty($pField->getLabelOnlyValues());
		$this->assertEquals(FieldTypes::FIELD_TYPE_VARCHAR, $pField->getType());
		$this->assertFalse($pField->getIsRangeField());
		$this->assertEmpty($pField->getRangeFieldTranslations());
		$this->assertEmpty($pField->getCompoundFields());
	}


	/**
	 *
	 */

	public function testSetter()
	{
		$pField = $this->getPrefilledField();
		$this->assertEquals('asdf', $pField->getCategory());
		$this->assertEquals('asd', $pField->getDefault());
		$this->assertEquals('A test', $pField->getLabel());
		$this->assertEquals(13, $pField->getLength());
		$this->assertEquals(['test', 'asdf', 13, 37], $pField->getPermittedvalues());
		$this->assertEquals(['asdf'], $pField->getLabelOnlyValues());
		$this->assertEquals(FieldTypes::FIELD_TYPE_DATE, $pField->getType());
		$this->assertTrue($pField->getIsRangeField());
		$this->assertSame([
			'testField123__von' => 'testField123 from',
			'testField123__bis' => 'testField123 up to',
		], $pField->getRangeFieldTranslations());
		$this->assertSame(['Anrede-Titel' => ['Anrede', 'Titel']], $pField->getCompoundFields());

	}


	/**
	 *
	 * This is pretty straightforward, but
	 *  - category is called 'content'
	 *  - length becomes null if it was set to 0
	 *
	 */

	public function testGetAsArray()
	{
		$pField = $this->getPrefilledField();
		$expectation = [
			'module' => 'testModuleA',
			'label' => 'A test',
			'type' => 'date',
			'default' => 'asd',
			'length' => 13,
			'permittedvalues' => ['test', 'asdf', 13, 37],
			'labelOnlyValues' => ['asdf'],
			'content' => 'asdf',
			'rangefield' => true,
			'additionalTranslations' => [
				'testField123__von' => 'testField123 from',
				'testField123__bis' => 'testField123 up to',
			],
			'compoundFields' => ['Anrede-Titel' => ['Anrede', 'Titel']],
			'tablename' => '',
			'dependencies' => []
		];
		$this->assertEquals($expectation, $pField->getAsRow());

		$pField->setLength(0);
		$expectation['length'] = null;

		$this->assertEquals($expectation, $pField->getAsRow());
	}

	/**
	 * @see ticket #1560699
	 */
	public function testCreateByRowWithEmptyLabel()
	{
		$row = [
			'module' => 'testModuleA',
			'label' => null,
			'type' => 'date',
			'default' => 'asd',
			'length' => 13,
			'permittedvalues' => ['test', 'asdf', 13, 37],
			'labelOnlyValues' => ['asdf'],
			'content' => 'asdf',
			'rangefield' => true,
			'additionalTranslations' => [
				'testField123__von' => 'testField123 from',
				'testField123__bis' => 'testField123 up to',
			],
			'compoundFields' => ['Anrede-Titel' => ['Anrede', 'Titel']],
		];
		$pField1 = Field::createByRow('testField1Name', $row);
		$this->assertEquals('(testField1Name)', $pField1->getLabel());
		$row['label'] = '';
		$pField2 = Field::createByRow('testField2Name', $row);
		$this->assertEquals('(testField2Name)', $pField2->getLabel());
	}

	/**
	 *
	 */
	public function testCreateByRow()
	{
		$row = [
			'module' => 'testModuleA',
			'label' => 'testField1Label',
			'type' => 'date',
			'default' => 'asd',
			'length' => 13,
			'permittedvalues' => ['test', 'asdf', 13, 37],
			'labelOnlyValues' => ['test'],
			'content' => 'asdf',
			'rangefield' => true,
			'additionalTranslations' => [
				'testField123__von' => 'testField123 from',
				'testField123__bis' => 'testField123 up to',
			],
			'compoundFields' => ['Anrede-Titel' => ['Anrede', 'Titel']],
			'tablename' => '',
			'dependencies' => [],
		];
		$pField = Field::createByRow('testField1Name', $row);
		$this->assertEquals($row, $pField->getAsRow());
	}


	/**
	 *
	 * @return Field
	 *
	 */

	private function getPrefilledField(): Field
	{
		$pField = new Field('testField123', 'testModuleA', 'A test');
		$pField->setCategory('asdf');
		$pField->setDefault('asd');
		$pField->setLength(13);
		$pField->setPermittedvalues(['test', 'asdf', 13, 37]);
		$pField->setType(FieldTypes::FIELD_TYPE_DATE);
		$pField->setIsRangeField(true);
		$pField->setRangeFieldTranslations([
			'testField123__von' => 'testField123 from',
			'testField123__bis' => 'testField123 up to',
		]);
		$pField->setLabelOnlyValues(['asdf']);
		$pField->setCompoundFields([
			'Anrede-Titel' => ['Anrede', 'Titel'],
		]);
		return $pField;
	}
}
