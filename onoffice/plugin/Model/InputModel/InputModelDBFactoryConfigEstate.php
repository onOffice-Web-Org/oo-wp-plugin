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
	implements InputModelDBFactoryConfigBase
{
	/** */
	const INPUT_FIELD_FILTERABLE = 'inputfilterable';

	/** If filterable, it can also be hidden */
	const INPUT_FIELD_HIDDEN = 'inputhidden';


	/** @var array */
	private $_inputConfig = array(
		InputModelDBFactory::INPUT_FILTERID => array(
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'filterId',
		),
		InputModelDBFactory::INPUT_LISTNAME => array(
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'name',
		),
		InputModelDBFactory::INPUT_RECORDS_PER_PAGE => array(
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'recordsPerPage',
		),
		InputModelDBFactory::INPUT_SORTBY => array(
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'sortby',
		),
		InputModelDBFactory::INPUT_SORTORDER => array(
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'sortorder',
		),
		InputModelDBFactory::INPUT_PICTURE_TYPE => array(
			self::KEY_TABLE => 'oo_plugin_picturetypes',
			self::KEY_FIELD => 'picturetype',
		),
		InputModelDBFactory::INPUT_TEMPLATE => array(
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'template',
		),
		InputModelDBFactory::INPUT_LIST_TYPE => array(
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'list_type',
		),
		InputModelDBFactory::INPUT_SHOW_STATUS => array(
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'show_status',
		),
		InputModelDBFactory::INPUT_EXPOSE => array(
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'expose',
		),
		InputModelDBFactory::INPUT_RANDOM_ORDER => array(
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'random',
		),
		InputModelDBFactory::INPUT_FIELD_CONFIG => array(
			self::KEY_TABLE => 'oo_plugin_fieldconfig',
			self::KEY_FIELD => 'fieldname',
		),
		self::INPUT_FIELD_FILTERABLE => array(
			self::KEY_TABLE => 'oo_plugin_fieldconfig',
			self::KEY_FIELD => 'filterable',
		),
		self::INPUT_FIELD_HIDDEN => array(
			self::KEY_TABLE => 'oo_plugin_fieldconfig',
			self::KEY_FIELD => 'hidden',
		),
	);


	/**
	 *
	 * @return array
	 *
	 */

	public function getConfig()
	{
		return $this->_inputConfig;
	}
}
