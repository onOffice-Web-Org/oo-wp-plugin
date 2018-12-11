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
use onOffice\WPlugin\GeoPosition;

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

	public static function registerScripts(string $module, array $distinctFields)
	{
		$pluginPath = ONOFFICE_PLUGIN_DIR.'/index.php';

		wp_register_script('setPossibleTypeValues', plugins_url('/js/distinctFields.js', $pluginPath));
		wp_enqueue_script('setPossibleTypeValues', plugins_url('/js/distinctFields.js', $pluginPath).'/distinctFields.js');

		wp_localize_script( 'setPossibleTypeValues', 'base_path',  plugins_url('/tools/distinctFields.php', $pluginPath));
		wp_localize_script( 'setPossibleTypeValues', 'distinctValues', json_encode($distinctFields));
		wp_localize_script( 'setPossibleTypeValues', 'module',  $module);
		wp_localize_script( 'setPossibleTypeValues', 'notSpecifiedLabel',  esc_html('Not Specified', 'onoffice'));
	}


	/**
	 *
	 */

	public function check()
	{
		$module = filter_input(INPUT_POST, DistinctFieldsHandler::PARAMETER_MODULE);
		$inputValues = json_decode(filter_input(INPUT_POST, DistinctFieldsHandler::PARAMETER_INPUT_VALUES), true);
		$this->_distinctFields = json_decode(filter_input(INPUT_POST, DistinctFieldsHandler::PARAMETER_DISTINCT_VALUES), true);
		$this->setInputValues($module, $inputValues);

		$pDistinctFieldsHandler = new DistinctFieldsHandler();
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