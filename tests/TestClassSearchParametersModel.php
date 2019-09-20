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
	 * @covers onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel::enableFilter
	 * @covers onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel::filterParameters
	 *
	 */

	public function testSetParameter()
	{
		$pInstance = new SearchParametersModel();
		$pInstance->setParameter('asd', 'valueAsd');

		$pInstance->enableFilter(false);
		$this->assertEquals(['asd' => 'valueAsd'], $pInstance->getParameters());

		$pInstance->enableFilter(true);
		$this->assertEquals([], $pInstance->getParameters());
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel::setParameters
	 * @covers onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel::getParameters
	 * @covers onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel::enableFilter
	 *
	 */

	public function testSetParameters()
	{
		$pInstance = new SearchParametersModel();
		$pInstance->setParameters(['asd' => 'qwer']);
		$pInstance->enableFilter(false);

		$this->assertEquals(['asd' => 'qwer'], $pInstance->getParameters());
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
	 * @covers onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel::enableFilter
	 * @covers onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel::getFilter
	 *
	 */

	public function testEnableFilter()
	{
		$pInstance = new SearchParametersModel();
		$pInstance->enableFilter(true);

		$this->assertEquals(true, $pInstance->getFilter());
	}
}