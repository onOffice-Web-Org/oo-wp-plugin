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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\DistinctFieldsScriptRegistrator;
use onOffice\WPlugin\WP\WPScriptStyleTest;
use WP_UnitTestCase;


/**
 *
 */

class TestClassDistinctFieldsScriptRegistrator
	extends WP_UnitTestCase
{
	/** */
	const DISTINCT_FIELDS = [
		'testField1',
		'testField2',
		'testField3',
	];


	/**
	 *
	 */

	public function testRegister()
	{
		$pWPScriptStyle = new WPScriptStyleTest();
		$pDistinctFieldsScriptRegistrator = new DistinctFieldsScriptRegistrator($pWPScriptStyle);
		$this->assertEmpty($pWPScriptStyle->getRegisteredScripts());
		$this->assertEmpty($pWPScriptStyle->getEnqueuedScripts());
		$this->assertEmpty($pWPScriptStyle->getLocalizedScripts());
		$pDistinctFieldsScriptRegistrator->registerScripts(onOfficeSDK::MODULE_ESTATE, self::DISTINCT_FIELDS);
		$this->assertEquals([
			'onoffice-distinctValues' => [
				'src' => 'http://example.org/wp-content/plugins'.getcwd().'/js/distinctFields.js',
				'deps' => ['jquery'],
				'ver' => false,
				'inFooter' => false,
			],
		], $pWPScriptStyle->getRegisteredScripts());
		$this->assertEquals(['onoffice-distinctValues'], $pWPScriptStyle->getEnqueuedScripts());
		$this->assertEquals([
			'onoffice-distinctValues' => [
				'name' => 'onoffice_distinctFields',
				'data' => [
					'base_path' => 'http://example.org/wp-admin/admin-ajax.php?action=distinctfields',
					'distinctValues' => self::DISTINCT_FIELDS,
					'module' => 'estate',
					'notSpecifiedLabel' => 'Not specified',
					'editValuesLabel' => 'Edit values',
				],
			]
		], $pWPScriptStyle->getLocalizedScripts());
	}


	/**
	 *
	 */

	public function testRegisterEmpty()
	{
		$pWPScriptStyle = new WPScriptStyleTest();
		$pDistinctFieldsScriptRegistrator = new DistinctFieldsScriptRegistrator($pWPScriptStyle);
		$pDistinctFieldsScriptRegistrator->registerScripts(onOfficeSDK::MODULE_ESTATE, []);
		$this->assertEmpty($pWPScriptStyle->getRegisteredScripts());
		$this->assertEmpty($pWPScriptStyle->getEnqueuedScripts());
		$this->assertEmpty($pWPScriptStyle->getLocalizedScripts());
	}
}
