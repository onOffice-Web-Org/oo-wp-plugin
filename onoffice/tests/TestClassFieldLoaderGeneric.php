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
use onOffice\WPlugin\Field\Collection\FieldLoaderGeneric;
use WP_UnitTestCase;
use function json_decode;


/**
 *
 */

class TestClassFieldLoaderGeneric
	extends WP_UnitTestCase
{
	/** @var FieldLoaderGeneric */
	private $_pFieldLoader = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$fieldParameters = [
			'labels' => true,
			'showContent' => true,
			'showTable' => true,
			'language' => 'ENG',
			'modules' => ['address', 'estate'],
		];
		$pSDKWrapper = new SDKWrapperMocker();
		$responseGetFields = json_decode
			(file_get_contents(__DIR__.'/resources/ApiResponseGetFields.json'), true);
		$pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'fields', '',
			$fieldParameters, null, $responseGetFields);
		$this->_pFieldLoader = new FieldLoaderGeneric($pSDKWrapper);
	}


	/**
	 *
	 */

	public function testLoad()
	{
		$result = iterator_to_array($this->_pFieldLoader->load());
		$this->assertCount(203, $result);

		foreach ($result as $fieldname => $fieldProperties) {
			$this->assertInternalType('string', $fieldname);
			$actualModule = $fieldProperties['module'];
			$this->assertContains($actualModule,
				[onOfficeSDK::MODULE_ADDRESS, onOfficeSDK::MODULE_ESTATE], 'Module: '.$actualModule);
			$this->assertArrayHasKey('module', $fieldProperties);
			$this->assertArrayHasKey('label', $fieldProperties);
			$this->assertArrayHasKey('type', $fieldProperties);
			$this->assertArrayHasKey('default', $fieldProperties);
			$this->assertArrayHasKey('length', $fieldProperties);
			$this->assertArrayHasKey('permittedvalues', $fieldProperties);
			$this->assertArrayHasKey('content', $fieldProperties);
		}
	}
}
