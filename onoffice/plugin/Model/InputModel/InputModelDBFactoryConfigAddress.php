<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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

namespace onOffice\WPlugin\Model\InputModel;

use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigBase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */
class InputModelDBFactoryConfigAddress
	implements InputModelDBFactoryConfigBase, InputModelDBFactoryFilterableFields
{
	/** @var array */
	private $_inputConfig = [
		InputModelDBFactory::INPUT_FILTERID => [
			self::KEY_TABLE => 'oo_plugin_listviews_address',
			self::KEY_FIELD => 'filterId',
		],
		InputModelDBFactory::INPUT_LISTNAME => [
			self::KEY_TABLE => 'oo_plugin_listviews_address',
			self::KEY_FIELD => 'name',
		],
		InputModelDBFactory::INPUT_RECORDS_PER_PAGE => [
			self::KEY_TABLE => 'oo_plugin_listviews_address',
			self::KEY_FIELD => 'recordsPerPage',
		],
		InputModelDBFactory::INPUT_SORTBY => [
			self::KEY_TABLE => 'oo_plugin_listviews_address',
			self::KEY_FIELD => 'sortby',
		],
		InputModelDBFactory::INPUT_SORTORDER => [
			self::KEY_TABLE => 'oo_plugin_listviews_address',
			self::KEY_FIELD => 'sortorder',
		],
		InputModelDBFactory::INPUT_PICTURE_TYPE => [
			self::KEY_TABLE => 'oo_plugin_listviews_address',
			self::KEY_FIELD => 'showPhoto',
		],
		InputModelDBFactory::INPUT_TEMPLATE => [
			self::KEY_TABLE => 'oo_plugin_listviews_address',
			self::KEY_FIELD => 'template',
		],
		InputModelDBFactory::INPUT_FIELD_CONFIG => [
			self::KEY_TABLE => 'oo_plugin_address_fieldconfig',
			self::KEY_FIELD => 'fieldname',
		],
		self::INPUT_FIELD_FILTERABLE => [
			self::KEY_TABLE => 'oo_plugin_address_fieldconfig',
			self::KEY_FIELD => 'filterable',
		],
		self::INPUT_FIELD_HIDDEN => [
			self::KEY_TABLE => 'oo_plugin_address_fieldconfig',
			self::KEY_FIELD => 'hidden',
		],
	];


	/**
	 *
	 * @return array
	 *
	 */

	public function getConfig(): array
	{
		return $this->_inputConfig;
	}
}
