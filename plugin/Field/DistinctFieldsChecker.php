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

namespace onOffice\WPlugin\Field;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\DistinctFieldsHandler;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\RequestVariablesSanitizer;
use onOffice\WPlugin\WP\WPScriptStyleBase;
use const ONOFFICE_PLUGIN_DIR;
use function __;
use function add_action;
use function plugins_url;

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

	/** @varRequestVariablesSanitizer */
	private $_pRequestVariables = null;

	/** @var WPScriptStyleBase */
	private $_pScriptStyle = null;


	/**
	 *
	 * @param RequestVariablesSanitizer $pRequestVariables
	 * @param WPScriptStyleBase $pScriptStyle
	 *
	 */

	public function __construct
			(RequestVariablesSanitizer $pRequestVariables, WPScriptStyleBase $pScriptStyle)
	{
		$this->_pRequestVariables = $pRequestVariables;
		$this->_pScriptStyle = $pScriptStyle;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getDistinctValues(): array
	{
		return $this->_pRequestVariables->getFilteredPost(
				DistinctFieldsHandler::PARAMETER_DISTINCT_VALUES, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getInputValues(): array
	{
		return json_decode($this->_pRequestVariables->getFilteredPost(
				DistinctFieldsHandler::PARAMETER_INPUT_VALUES), true) ?? [];
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getModule(): string
	{
		return $this->_pRequestVariables->getFilteredPost(DistinctFieldsHandler::PARAMETER_MODULE) ?? '';
	}


	/**
	 *
	 * @return WPScriptStyleBase
	 *
	 */

	public function getScriptStyle(): WPScriptStyleBase
		{ return $this->_pScriptStyle; }


	/**
	 *
	 */

	public function addHook()
	{
		add_action('wp_ajax_check_sandbox_data', function() {
			$this->check();
		});
	}


	/**
	 *
	 * @param string $module
	 * @param array $distinctFields
	 * @return void
	 *
	 */

	public function registerScripts(string $module, array $distinctFields)
	{
		if ($distinctFields === []) {
			return;
		}

		$pluginPath = ONOFFICE_PLUGIN_DIR.'/index.php';
		$pScriptStyle = $this->_pScriptStyle;
		$values = [
			'base_path' => plugins_url('/tools/distinctFields.php', $pluginPath),
			'distinctValues' => $distinctFields,
			'module' => $module,
			'notSpecifiedLabel' => __('Not specified', 'onoffice'),
			'editValuesLabel' => __('Edit values', 'onoffice'),
		];

		$pScriptStyle->registerScript('onoffice-distinctValues', plugins_url('/js/distinctFields.js', $pluginPath));
		$pScriptStyle->enqueueScript('onoffice-distinctValues');
		$pScriptStyle->localizeScript('onoffice-distinctValues', 'onoffice_distinctFields', $values);
	}


	/**
	 *
	 */

	public function check(): array
	{
		$module = $this->getModule();
		$inputValues = $this->getInputValues();
		$this->_distinctFields = $this->getDistinctValues();

		$this->setInputValues($module, $inputValues);

		$pDistinctFieldsHandler = new DistinctFieldsHandler();
		$pDistinctFieldsHandler->setModule($module);
		$pDistinctFieldsHandler->setDistinctFields($this->_distinctFields);
		$pDistinctFieldsHandler->setInputValues($this->_fieldValues);
		$pDistinctFieldsHandler->setGeoPositionFields($this->_geoRangeValues);

		$pDistinctFieldsHandler->check();

		return $pDistinctFieldsHandler->getValues();
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

		if ($module === onOfficeSDK::MODULE_ESTATE) {
			foreach ($inputValues as $key => $values) {
				if (in_array($key, $pGeoPosition->getEstateSearchFields())) {
					$this->_geoRangeValues[$key] = $values;
					unset($inputValues[$key]);
				}
			}
		} elseif ($module == onOfficeSDK::MODULE_SEARCHCRITERIA) {
			$mapping = array_flip($pGeoPosition->getSearchCriteriaFields());
			foreach ($inputValues as $key => $values) {
				if (isset($mapping[$key])) {
					$this->_geoRangeValues[$mapping[$key]] = $values;
					unset($inputValues[$key]);
				}
			}
		}

		$this->_fieldValues = $inputValues;
	}


	/** @return array */
	public function getDistinctValuesFields(): array
		{ return $this->_distinctFields; }
}