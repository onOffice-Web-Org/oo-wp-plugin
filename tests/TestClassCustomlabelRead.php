<?php

/**
 *
 *    Copyright (C) 2021 onOffice GmbH
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
use onOffice\WPlugin\Field\CustomLabel\CustomLabelModelBool;
use onOffice\WPlugin\Field\CustomLabel\CustomLabelModelField;
use onOffice\WPlugin\Field\CustomLabel\CustomLabelRead;
use onOffice\WPlugin\Types\Field;
use WP_UnitTestCase;
use wpdb;

/**
 *
 */
class TestClassCustomLabelRead
	extends WP_UnitTestCase
{
	/** @var CustomLabelRead */
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

		$this->_pSubject = new CustomLabelRead($this->_pWPDBMock);
	}


	/**
	 *
	 * @dataProvider dataProviderField
	 * @param int $formId
	 * @param array $rows
	 *
	 */

	public function testReadCustomLabelsField(int $formId, array $rows)
	{
		$this->_pWPDBMock->expects($this->once())->method('get_results')->will($this->returnValue($rows));
		$pField = new Field('testField', 'testModule');
		$pResult = $this->_pSubject->readCustomLabelsField($formId, [$pField],'oo_plugin_fieldconfig_form_customs_labels','oo_plugin_fieldconfig_form_translated_labels');

		$this->assertEquals($rows, $pResult);
	}

	/**
	 *
	 * @return array
	 *
	 */

	public function dataProviderField()
	{
		$rows = [
			[123, ['customs_labels_id' => 1337, 'locale' => 'de_DE', 'value' => 'Deutschland', 'fieldname' => 'testField']]
		];

		return $rows;
	}

	/**
	 *
	 */
	public function testGetCustomLabelsFieldsForAdmin()
	{
		$pField = new Field('testField', 'testModule');
		$row = [
			(object)[
				'defaults_id' => '13',
				'value' => 'Spider Man',
				'fieldname' => 'testField',
				'type' => 'text',
				'locale' => 'en_US',
			],
		];

		$this->_pWPDBMock->expects($this->once())->method('get_results')->will($this->returnValue($row));
		$result = $this->_pSubject->getCustomLabelsFieldsForAdmin(13, [$pField], 'en_US', 'oo_plugin_fieldconfig_form_customs_labels','oo_plugin_fieldconfig_form_translated_labels');

		$this->assertEquals(['testField' => ['native' => 'Spider Man']], $result);
	}
}
