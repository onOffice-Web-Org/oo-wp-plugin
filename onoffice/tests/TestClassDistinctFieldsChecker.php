<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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

use onOffice\WPlugin\Field\DistinctFieldsChecker;
use onOffice\WPlugin\Field\DistinctFieldsCheckerEnvironment;
use onOffice\WPlugin\Field\DistinctFieldsCheckerEnvironmentTest;
use onOffice\WPlugin\WP\WPScriptStyleBase;
use onOffice\WPlugin\WP\WPScriptStyleTest;
use WP_UnitTestCase;

/**
 *
 * test class of DistinctFieldsChecker
 *
 */

class TestClassDistinctFieldsChecker
	extends WP_UnitTestCase
{
	/** @var DistinctFieldsCheckerEnvironment */
	private $_pTestEnvironment = null;

	/** @var WPScriptStyleBase */
	private $_pWScriptStyle = null;

	/** @var DistinctFieldsChecker */
	private $_pChecker = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$inputValues = [
			'' => 'Send',
			'country' => '',
			'objektart[]' => ['wohnung'],
			"radius" => '',
			'vermarktungsart[]' => ['kauf', 'miete'],
			'wohnflaeche__bis' => '300',
			'wohnflaeche__von' => '100',
		];

		$this->_pTestEnvironment = new DistinctFieldsCheckerEnvironmentTest();
		$this->_pTestEnvironment->setModule('estate');
		$this->_pTestEnvironment->setDistictValues(['objektart','objekttyp']);
		$this->_pTestEnvironment->setInputValues($inputValues);
		$this->_pTestEnvironment->setScriptStyle(new WPScriptStyleTest());

		$this->_pWScriptStyle = $this->_pTestEnvironment->getScriptStyle();
		$this->_pChecker = new DistinctFieldsChecker($this->_pTestEnvironment);
	}
}