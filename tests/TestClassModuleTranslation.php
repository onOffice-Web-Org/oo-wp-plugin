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
use onOffice\WPlugin\Translation\ModuleTranslation;


/**
 *
 */

class TestClassModuleTranslation
	extends WP_UnitTest_Localized
{

	/**
	 *
	 * @dataProvider getLabelsUS
	 * @param string $input
	 * @param string $expectation
	 *
	 */

	public function testGetlabelSingularUS(string $input, string $expectation)
	{
		$this->switchLocale('en_US');
		$this->assertSame($expectation, ModuleTranslation::getLabelSingular($input));
	}


	/**
	 *
	 *
	 * @dataProvider getLabelsDE
	 * @param string $input
	 * @param string $expectation
	 *
	 */

	public function testGetlabelSingularDE(string $input, string $expectation)
	{
		$this->switchLocale('de_DE');
		$this->assertSame($expectation, ModuleTranslation::getLabelSingular($input));
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getLabelsUS(): array
	{
		return [
			[onOfficeSDK::MODULE_ADDRESS, 'Address'],
			[onOfficeSDK::MODULE_ESTATE, 'Estate'],
			[onOfficeSDK::MODULE_SEARCHCRITERIA, 'Search Criteria'],
			['unknown', ''],
		];
	}
	/**
	 *
	 * @return array
	 *
	 */

	public function getLabelsDE(): array
	{
		return [
			[onOfficeSDK::MODULE_ADDRESS, 'Adresse'],
			[onOfficeSDK::MODULE_ESTATE, 'Immobilie'],
			[onOfficeSDK::MODULE_SEARCHCRITERIA, 'Suchkriterium'],
			['unknown', ''],
		];
	}


	/**
	 *
	 */

	public function testGetAllLabelsSingularUS()
	{
		$this->switchLocale('en_US');
		$expectedResult = [
			onOfficeSDK::MODULE_ADDRESS => 'Address',
			onOfficeSDK::MODULE_ESTATE => 'Estate',
			onOfficeSDK::MODULE_SEARCHCRITERIA => 'Search Criteria',
		];
		$this->assertEquals($expectedResult, ModuleTranslation::getAllLabelsSingular());
	}


	/**
	 *
	 */

	public function testGetAllLabelsSingularDE()
	{
		$this->switchLocale('de_DE');
		$expectedResult = [
			onOfficeSDK::MODULE_ADDRESS => 'Adresse',
			onOfficeSDK::MODULE_ESTATE => 'Immobilie',
			onOfficeSDK::MODULE_SEARCHCRITERIA => 'Suchkriterium',
		];
		$this->assertEquals($expectedResult, ModuleTranslation::getAllLabelsSingular());
	}
}
