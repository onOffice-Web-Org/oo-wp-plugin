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

namespace onOffice\WPlugin\Field;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\DistinctFieldsHandler;
use onOffice\WPlugin\Field\DistinctFieldsHandlerConfigurationDefault;
use onOffice\WPlugin\Field\DistinctFieldsCheckerEnvironment;
use onOffice\WPlugin\GeoPosition;
use const ONOFFICE_PLUGIN_DIR;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class DistinctFieldsChecker
{
	/** @var array */
	private $_fieldValues = [];

	/** @var array */
	private $_distinctFields = [];

	/** @var array */
	private $_geoRangeValues = [];

	/** @var DistinctFieldsCheckerEnvironment */
	private $_pEnvironment = null;

	/** @var array */
	static private $_mapping =
			[
				'range_land' => 'country',
				'range_plz' => 'zip',
				'range_strasse' => 'street',
				'range' => 'radius',
			];


	/**
	 *
	 * @param DistinctFieldsCheckerEnvironment $pDistinctFieldsCheckerEnvironment
	 *
	 */

	public function __construct(
		DistinctFieldsCheckerEnvironment $pDistinctFieldsCheckerEnvironment = null)
	{
		$this->_pEnvironment =
			$pDistinctFieldsCheckerEnvironment ?? new DistinctFieldsCheckerEnvironmentDefault();
	}


	/**
	 *
	 */

	public static function addHook()
	{
		add_action('wp_ajax_check_sandbox_data', function() {
			$pDistinctFieldsChecker = new DistinctFieldsChecker();
			$pDistinctFieldsChecker->check();
		});
	}


	/**
	 *
	 * @param string $module
	 * @param array $distinctFields
	 *
	 */

	public function registerScripts(string $module, array $distinctFields)
	{
		$pluginPath = ONOFFICE_PLUGIN_DIR.'/index.php';
		$pScriptStyle = $this->_pEnvironment->getScriptStyle();

		$pScriptStyle->registerScript('setPossibleTypeValues', plugins_url('/js/distinctFields.js', $pluginPath));
		$pScriptStyle->enqueueScript('setPossibleTypeValues', plugins_url('/js/distinctFields.js', $pluginPath).'/distinctFields.js');
		$pScriptStyle->localizeScript('setPossibleTypeValues', 'base_path',  [plugins_url('/tools/distinctFields.php', $pluginPath)]);
		$pScriptStyle->localizeScript('setPossibleTypeValues', 'distinctValues', [json_encode($distinctFields)]);
		$pScriptStyle->localizeScript('setPossibleTypeValues', 'module',  [$module]);
		$pScriptStyle->localizeScript('setPossibleTypeValues', 'notSpecifiedLabel',  [esc_html('Not Specified', 'onoffice')]);
	}


	/**
	 *
	 */

	public function check()
	{
		$module = $this->_pEnvironment->getModule();
		$inputValues = $this->_pEnvironment->getInputValues();
		$this->_distinctFields = $this->_pEnvironment->getDistinctValues();
		$this->setInputValues($module, $inputValues);

		$pDistinctFieldsHandler = new DistinctFieldsHandler(new DistinctFieldsHandlerConfigurationDefault());
		$pDistinctFieldsHandler->setModule($module);
		$pDistinctFieldsHandler->setDistinctFields($this->_distinctFields);
		$pDistinctFieldsHandler->setInputValues($this->_fieldValues);
		$pDistinctFieldsHandler->setGeoPositionFields($this->_geoRangeValues);

		$pDistinctFieldsHandler->check();
		$result = $pDistinctFieldsHandler->getValues();

		echo json_encode($result);
		die;
		wp_die();
	}



	/**
	 *
	 * @param string $module
	 * @param array $inputValues
	 *
	 */

	private function setInputValues(string $module, array $inputValues)
	{
		$pGeoPosition = new GeoPosition();

		if ($module == onOfficeSDK::MODULE_ESTATE)
		{
			foreach ($inputValues as $key => $values)
			{
				if (in_array($key, $pGeoPosition->getEstateSearchFields()))
				{
					$this->_geoRangeValues[$key] = $values;
					unset($inputValues[$key]);
				}
			}
		}
		elseif ($module == onOfficeSDK::MODULE_SEARCHCRITERIA)
		{
			foreach ($inputValues as $key => $values)
			{
				if (in_array($key, $pGeoPosition->getSettingsGeoPositionFields($module)))
				{
					$this->_geoRangeValues[self::$_mapping[$key]] = $values;
					unset($inputValues[$key]);
				}
			}
		}

		$this->_fieldValues = $inputValues;
	}


	/** return  array */
	public function getDistinctValuesFields()
	{ return $this->_distinctFields; }
}