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


/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class InputModelDBFactoryConfigEstate
	implements InputModelDBFactoryConfigBase, InputModelDBFactoryFilterableFields
{
	/** @var array */
	private $_inputConfig = [
		InputModelDBFactory::INPUT_FILTERID => [
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'filterId',
		],
		InputModelDBFactory::INPUT_LISTNAME => [
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'name',
		],
		InputModelDBFactory::INPUT_RECORDS_PER_PAGE => [
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'recordsPerPage',
		],
		InputModelDBFactory::INPUT_SORTBY => [
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'sortby',
		],
		InputModelDBFactory::INPUT_SORTORDER => [
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'sortorder',
		],
		InputModelDBFactory::INPUT_PICTURE_TYPE => [
			self::KEY_TABLE => 'oo_plugin_picturetypes',
			self::KEY_FIELD => 'picturetype',
		],
		InputModelDBFactory::INPUT_TEMPLATE => [
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'template',
		],
		InputModelDBFactory::INPUT_LIST_TYPE => [
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'list_type',
		],
		InputModelDBFactory::INPUT_SHOW_STATUS => [
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'show_status',
		],
		InputModelDBFactory::INPUT_SHOW_REFERENCE_ESTATE => [
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'show_reference_estate',
		],
		InputModelDBFactory::INPUT_EXPOSE => [
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'expose',
		],
		InputModelDBFactory::INPUT_RANDOM_ORDER => [
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'random',
		],
		InputModelDBFactory::INPUT_FIELD_CONFIG => [
			self::KEY_TABLE => 'oo_plugin_fieldconfig',
			self::KEY_FIELD => 'fieldname',
		],
		self::INPUT_FIELD_FILTERABLE => [
			self::KEY_TABLE => 'oo_plugin_fieldconfig',
			self::KEY_FIELD => 'filterable',
		],
		self::INPUT_FIELD_HIDDEN => [
			self::KEY_TABLE => 'oo_plugin_fieldconfig',
			self::KEY_FIELD => 'hidden',
		],
		self::INPUT_FIELD_AVAILABLE_OPTIONS => [
			self::KEY_TABLE => 'oo_plugin_fieldconfig',
			self::KEY_FIELD => 'availableOptions',
		],

		InputModelDBFactory::INPUT_SORT_BY_SETTING => [
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'sortBySetting',
		],

		InputModelDBFactory::INPUT_SORT_BY_CHOSEN => [
			self::KEY_TABLE => 'oo_plugin_sortbyuservalues',
			self::KEY_FIELD => 'sortbyuservalue',
		],

		InputModelDBFactory::INPUT_SORT_BY_DEFAULT => [
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'sortByUserDefinedDefault',
		],

		InputModelDBFactory::INPUT_SORT_BY_USER_DEFINED_DIRECTION => [
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'sortByUserDefinedDirection',
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
