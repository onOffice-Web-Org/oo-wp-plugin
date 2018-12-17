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


use onOffice\WPlugin\Field\DistinctFieldsCheckerEnvironment;
use onOffice\WPlugin\Field\DistinctFieldsCheckerEnvironmentTest;
use onOffice\WPlugin\WP\WPScriptStyleBase;
use onOffice\WPlugin\WP\WPScriptStyleTest;
use onOffice\WPlugin\Field\DistinctFieldsChecker;

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

	private $_pChecker = null;



	public function setUp()
	{
		$inputValues = [
			"" => "Send",
			"country" => "",
			"objektart[]" => ['wohnung'],
			"radius" => "",
			"vermarktungsart[]" => ['kauf', 'miete'],
			"wohnflaeche__bis" => "300",
			"wohnflaeche__von" => "100",
			];

		$this->_pTestEnvironment = new DistinctFieldsCheckerEnvironmentTest();
		$this->_pTestEnvironment->setModule('estate');
		$this->_pTestEnvironment->setDistictValues(['objektart','objekttyp']);
		$this->_pTestEnvironment->setInputValues($inputValues);
		$this->_pTestEnvironment->setScriptStyle(new WPScriptStyleTest());

		$this->_pWScriptStyle = $this->_pTestEnvironment->getScriptStyle();

		$this->_pChecker = new DistinctFieldsChecker($this->_pTestEnvironment);

		parent::setUp();
	}


	/**
	 *
	 */

	public function testRegisterScripts()
	{
		$pluginPath = ONOFFICE_PLUGIN_DIR.'/index.php';

		$this->_pWScriptStyle->registerScript('setPossibleTypeValues', plugins_url('/js/distinctFields.js', $pluginPath));
		$this->_pWScriptStyle->enqueueScript('setPossibleTypeValues', plugins_url('/js/distinctFields.js', $pluginPath).'/distinctFields.js');
		$this->_pWScriptStyle->localizeScript('setPossibleTypeValues', 'base_path',  [plugins_url('/tools/distinctFields.php', $pluginPath)]);
		$this->_pWScriptStyle->localizeScript('setPossibleTypeValues', 'distinctValues', [json_encode($this->_pTestEnvironment->getDistinctValues())]);
		$this->_pWScriptStyle->localizeScript('setPossibleTypeValues', 'module',  [$this->_pTestEnvironment->getModule()]);
		$this->_pWScriptStyle->localizeScript('setPossibleTypeValues', 'notSpecifiedLabel',  [esc_html('Not Specified', 'onoffice')]);
	}
}