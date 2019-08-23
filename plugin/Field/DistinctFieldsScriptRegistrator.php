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

namespace onOffice\WPlugin\Field;

use onOffice\WPlugin\WP\WPScriptStyleBase;
use const ONOFFICE_PLUGIN_DIR;
use function __;
use function add_query_arg;
use function admin_url;
use function plugins_url;


/**
 *
 * @deprecated
 *
 */

class DistinctFieldsScriptRegistrator
{
	/** @var WPScriptStyleBase */
	private $_pScriptStyle;


	/**
	 *
	 * @param WPScriptStyleBase $pWPScriptStyle
	 *
	 */

	public function __construct(WPScriptStyleBase $pWPScriptStyle)
	{
		$this->_pScriptStyle = $pWPScriptStyle;
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

		$this->_pScriptStyle->registerScript('onoffice-distinctValues',
			plugins_url('/js/distinctFields.js', ONOFFICE_PLUGIN_DIR.'/index.php'), ['jquery']);
		$this->_pScriptStyle->enqueueScript('onoffice-distinctValues');
		$this->_pScriptStyle->localizeScript('onoffice-distinctValues', 'onoffice_distinctFields', [
			'base_path' => add_query_arg('action', 'distinctfields', get_site_url(null, '/distinctfields-json')),
			'distinctValues' => $distinctFields,
			'module' => $module,
			'notSpecifiedLabel' => __('Not specified', 'onoffice'),
			'editValuesLabel' => __('Edit values', 'onoffice'),
		]);
	}
}
