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

use onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel;
use WP_UnitTestCase;


class TestClassSearchParametersModel
	extends WP_UnitTestCase
{

	/**
	 *
	 * @covers onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel::setParameter
	 * @covers onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel::getParameters
	 * @covers onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel::filterParameters
	 *
	 */

	public function testSetParameter()
	{
		$pInstance = new SearchParametersModel();
		$pInstance->setParameter('asd', 'valueAsd');

		$this->assertEquals([], $pInstance->getParameters());
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel::setParameters
	 * @covers onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel::getParameters
	 *
	 */

	public function testSetParameters()
	{
		$pInstance = new SearchParametersModel();
		$pInstance->setParameters(['asd' => 'qwer']);

		$this->assertEquals([], $pInstance->getParameters());
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel::setParameterArray
	 * @covers onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel::getParameters
	 *
	 */

	public function testSetParameterArray()
	{
		$pInstance = new SearchParametersModel();
		$pInstance->setParameterArray('asd', ['val1', 'val2']);
		$pInstance->setAllowedGetParameters(['asd']);

		$this->assertEquals(['asd' => ['val1', 'val2']], $pInstance->getParameters());

		$pInstance = new SearchParametersModel();
		$pInstance->setParameterArray('asd', ['']);
		$pInstance->setAllowedGetParameters(['asd']);

		$this->assertEquals([], $pInstance->getParameters());
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel::addAllowedGetParameter
	 * @covers onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel::getAllowedGetParameters
	 *
	 */

	public function testAddAllowedGetParameter()
	{
		$pInstance = new SearchParametersModel();
		$pInstance->addAllowedGetParameter('asd');

		$this->assertEquals(['asd'], $pInstance->getAllowedGetParameters());
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel::setAllowedGetParameters
	 * @covers onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel::getAllowedGetParameters
	 *
	 */

	public function testSetAllowedGetParametes()
	{
		$pInstance = new SearchParametersModel();
		$pInstance->setAllowedGetParameters(['asd']);

		$this->assertEquals(['asd'], $pInstance->getAllowedGetParameters());
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel::populateDefaultLinkParams
	 * @covers onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel::getDefaultLinkParams
	 *
	 */
	public function testPopulateDefaultLinkParams()
	{
		$pInstance = new SearchParametersModel();
		$pInstance->populateDefaultLinkParams(['asd' => 'qwer']);

		$this->assertEquals(['asd' => 'qwer'], $pInstance->getDefaultLinkParams());
	}
}