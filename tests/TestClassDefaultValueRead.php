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
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelBool;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelMultiselect;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelNumericRange;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelSingleselect;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelText;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueRead;
use onOffice\WPlugin\Types\Field;
use WP_UnitTestCase;
use wpdb;

/**
 *
 */

class TestClassDefaultValueRead
	extends WP_UnitTestCase
{
	/** @var DefaultValueRead */
	private $_pSubject = null;

	/** @var wpdb */
	private $_pWPDBMock = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pWPDBMock = $this->getMockBuilder(\wpdb::class)
			->disableOriginalConstructor()
			->setMethods(['get_row', 'get_results'])
			->getMock();

		$this->_pSubject = new DefaultValueRead($this->_pWPDBMock);
	}


	/**
	 *
	 * @dataProvider dataProviderSingleSelect
	 * @param int $formId
	 * @param int $defaultValueId
	 * @param string $value
	 *
	 */

	public function testReadDefaultValuesSingleselect(int $formId, int $defaultValueId, string $value)
	{
		$row = [
			'defaults_id' => $defaultValueId,
			'value' => $value,
		];
		$this->_pWPDBMock->expects($this->once())->method('get_row')->will($this->returnValue($row));
		$pField = new Field('testField', 'testModule');
		$pExpectedDataModel = new DefaultValueModelSingleselect($formId, $pField);
		$pExpectedDataModel->setValue($value);
		$pExpectedDataModel->setDefaultsId($defaultValueId);
		$pResult = $this->_pSubject->readDefaultValuesSingleselect($formId, $pField);
		$this->assertInstanceOf(DefaultValueModelSingleselect::class, $pResult);
		$this->assertEquals($pExpectedDataModel, $pResult);
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function dataProviderSingleSelect(): array
	{
		return [
			[13, 1337, 'SpiderMan'],
			[14, 1338, 'SuperMan'],
		];
	}

	/**
	 * @dataProvider dataProviderMultiSelect
	 * @param int $formId
	 * @param array $rows
	 * @param DefaultValueModelMultiselect $pReference
	 */
	public function testReadDefaultValuesMultiSelect(int $formId, array $rows, DefaultValueModelMultiselect $pReference)
	{
		$this->_pWPDBMock->expects($this->once())->method('get_results')->will($this->returnValue($rows));
		$pField = new Field('testField', 'testModule');
		$pResult = $this->_pSubject->readDefaultValuesMultiSelect($formId, $pField);
		$this->assertInstanceOf(DefaultValueModelMultiselect::class, $pResult);
		$this->assertEquals($pReference, $pResult);
	}

	/**
	 * @return Generator
	 */
	public function dataProviderMultiSelect(): Generator
	{
		$pField = new Field('testField', 'testModule');

		$rows = [];
		$pReference1 = new DefaultValueModelMultiselect(12, $pField);
		yield [12, $rows, $pReference1];
		$pReference2 = new DefaultValueModelMultiselect(13, $pField);
		$pReference2->setValues(['Spider Man', 'Superman', 'Batman']);

		$rows = [
			[
				'defaults_id' => 1337,
				'locale' => '',
				'value' => 'Spider Man',
			], [
				'defaults_id' => 1338,
				'locale' => '',
				'value' => 'Superman',
			], [
				'defaults_id' => 1339,
				'locale' => '',
				'value' => 'Batman',
			],
		];
		yield [13, $rows, $pReference2];
	}

	/**
	 *
	 * @dataProvider dataProviderText
	 * @param int $formId
	 * @param array $rows
	 * @param DefaultValueModelText $pReference
	 *
	 */

	public function testReadDefaultValuesText(int $formId, array $rows, DefaultValueModelText $pReference)
	{
		$this->_pWPDBMock->expects($this->once())->method('get_results')->will($this->returnValue($rows));
		$pField = new Field('testField', 'testModule');

		$pResult = $this->_pSubject->readDefaultValuesText($formId, $pField);
		$this->assertInstanceOf(DefaultValueModelText::class, $pResult);
		$this->assertEquals($pReference, $pResult);
	}

	/**
	 *
	 * @return Generator
	 *
	 */

	public function dataProviderText(): Generator
	{
		$pField = new Field('testField', 'testModule');

		$rows = [];
		$pReference1 = new DefaultValueModelText(12, $pField);
		yield [12, $rows, $pReference1];
		$pReference2 = new DefaultValueModelText(13, $pField);
		$pReference2->addValueByLocale('de_DE', 'Deutschland');
		$pReference2->addValueByLocale('en_US', 'United States');
		$pReference2->addValueByLocale('fr_BE', 'Belgique');

		$rows = [
			(object)[
				'defaults_id' => 1337,
				'locale' => 'de_DE',
				'value' => 'Deutschland',
			],(object)[
				'defaults_id' => 1338,
				'locale' => 'en_US',
				'value' => 'United States',
			],(object)[
				'defaults_id' => 1339,
				'locale' => 'fr_BE',
				'value' => 'Belgique',
			],
		];
		yield [13, $rows, $pReference2];
	}

	/**
	 * @dataProvider dataProviderNumericRange
	 * @param int $formId
	 * @param array $rows
	 * @param DefaultValueModelNumericRange $pReference
	 */
	public function testReadDefaultValuesNumericRange(int $formId, array $rows, DefaultValueModelNumericRange $pReference)
	{
		$this->_pWPDBMock->expects($this->once())->method('get_results')->will($this->returnValue($rows));
		$pField = new Field('testField', 'testModule');

		$pResult = $this->_pSubject->readDefaultValuesNumericRange($formId, $pField);
		$this->assertInstanceOf(DefaultValueModelNumericRange::class, $pResult);
		$this->assertEquals($pReference, $pResult);
	}

	/**
	 * @return array
	 */
	public function dataProviderNumericRange(): array
	{
		$pField = new Field('testField', 'testModule');
		$pReference1 = new DefaultValueModelNumericRange(13, $pField);
		$pReference1->setValueFrom(.5);
		$pReference1->setValueTo(1337.7);
		$row = [
			(object)[
				'defaults_id' => 1333,
				'locale' => '',
				'value' => .5,
			], (object)[
				'defaults_id' => 1344,
				'value' => 1337.7,
				'locale' => '',
			],
		];
		return [
			[13, $row, $pReference1],
		];
	}

	/**
	 * @dataProvider dataProviderBool
	 * @param int $formId
	 * @param array $row
	 * @param bool $expectedResult
	 */
	public function testReadDefaultValuesBool(int $formId, array $row, bool $expectedResult)
	{
		$this->_pWPDBMock->expects($this->once())->method('get_row')->will($this->returnValue($row));
		$pField = new Field('testField', 'testModule');
		$pExpectedDataModel = new DefaultValueModelBool($formId, $pField);
		$pExpectedDataModel->setValue($expectedResult);
		$pExpectedDataModel->setDefaultsId((int)$row['defaults_id']);
		$pResult = $this->_pSubject->readDefaultValuesBool($formId, $pField);
		$this->assertInstanceOf(DefaultValueModelBool::class, $pResult);
		$this->assertEquals($pExpectedDataModel, $pResult);
	}

	/**
	 * @return array
	 */
	public function dataProviderBool(): array
	{
		return [
			[123, ['defaults_id' => '1334', 'value' => '0'], false],
			[123, ['defaults_id' => '1334', 'value' => '1'], true],
		];
	}

	/**
	 *
	 * @dataProvider dataProviderSingleSelect
	 * @param int $formId
	 * @param int $defaultValueId
	 * @param string $value
	 *
	 */

	public function testReadDefaultMultiValuesSingleSelect(int $formId, int $defaultValueId, string $value)
	{
		$row = (object)[
			'defaults_id' => $defaultValueId,
			'value' => $value,
 		];
		$this->_pWPDBMock->expects($this->once())->method('get_results')->will($this->returnValue([$row]));
		$pField = new Field('testField', 'testModule');
		$pResult = $this->_pSubject->readDefaultMultiValuesSingleSelect($formId, [$pField]);
		$this->assertEquals([$row], $pResult);
	}
}
