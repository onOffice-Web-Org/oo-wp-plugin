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
use onOffice\WPlugin\Field\Collection\FieldLoaderSearchCriteria;
use onOffice\WPlugin\Field\Collection\FieldRowConverterSearchCriteria;
use onOffice\WPlugin\GeoPosition;
use WP_UnitTestCase;
use function json_decode;


/**
 *
 */

class TestClassFieldLoaderSearchCriteria
	extends WP_UnitTestCase
{
	/** @var FieldLoaderSearchCriteria */
	private $_pFieldLoaderSearchCriteria = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$pSDKWrapper = new SDKWrapperMocker();
		$searchCriteriaFieldsParameters = ['language' => 'ENG', 'additionalTranslations' => true];
		$responseGetSearchcriteriaFields = json_decode
			(file_get_contents(__DIR__.'/resources/ApiResponseGetSearchcriteriaFieldsENG.json'), true);
		$pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'searchCriteriaFields', '',
			$searchCriteriaFieldsParameters, null, $responseGetSearchcriteriaFields);

		$pFieldRowConverter = new FieldRowConverterSearchCriteria();
		$this->_pFieldLoaderSearchCriteria = new FieldLoaderSearchCriteria($pSDKWrapper, $pFieldRowConverter);
	}


	/**
	 *
	 */

	public function testLoad()
	{
		$result = iterator_to_array($this->_pFieldLoaderSearchCriteria->load());
		$this->assertCount(12, $result);
		$this->assertArrayHasKey(GeoPosition::FIELD_GEO_POSITION, $result);

		foreach ($result as $fieldname => $fieldProperties) {
			$this->assertInternalType('string', $fieldname);
			$actualModule = $fieldProperties['module'];
			$this->assertEquals($actualModule, onOfficeSDK::MODULE_SEARCHCRITERIA);
			$this->assertArrayHasKey('module', $fieldProperties);
			$this->assertArrayHasKey('label', $fieldProperties);
			$this->assertArrayHasKey('type', $fieldProperties);
			$this->assertArrayHasKey('permittedvalues', $fieldProperties);
			$this->assertInternalType('array', $fieldProperties['permittedvalues']);
			$this->assertArrayHasKey('content', $fieldProperties);
			$this->assertArrayHasKey('rangefield', $fieldProperties);

			if ($fieldProperties['rangefield']) {
				$this->assertArrayHasKey('additionalTranslations', $fieldProperties);
			}
		}
	}
}
