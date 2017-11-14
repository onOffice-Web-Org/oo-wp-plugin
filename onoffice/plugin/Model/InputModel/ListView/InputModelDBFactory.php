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

namespace onOffice\WPlugin\Model\InputModel\ListView;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class InputModelDBFactory
{
	/** */
	const INPUT_FILTERID = 'filterId';

	/** */
	const INPUT_LISTNAME = 'listName';

	/** */
	const INPUT_RECORDS_PER_PAGE = 'recordsPerPage';

	/** */
	const INPUT_SORTBY = 'sortBy';

	/** */
	const INPUT_SORTORDER = 'sortOrder';

	/** */
	const INPUT_PICTURE_TYPE = 'pictureType';

	/** */
	const INPUT_TEMPLATE = 'template';

	/** */
	const INPUT_LIST_TYPE = 'listType';

	/** */
	const INPUT_SHOW_STATUS = 'showStatus';

	/** */
	const INPUT_RANDOM_ORDER = 'randomOrder';

	/** */
	const INPUT_EXPOSE = 'expose';

	/** */
	const INPUT_FIELD_CONFIG = 'fieldConfig';

	/** */
	const KEY_FIELD = 'field';

	/** */
	const KEY_TABLE = 'table';



	/** @var array */
	private $_inputConfig = array(
		self::INPUT_FILTERID => array(
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'filterId',
		),
		self::INPUT_LISTNAME => array(
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'name',
		),
		self::INPUT_RECORDS_PER_PAGE => array(
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'recordsPerPage',
		),
		self::INPUT_SORTBY => array(
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'sortby',
		),
		self::INPUT_SORTORDER => array(
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'sortorder',
		),
		self::INPUT_PICTURE_TYPE => array(
			self::KEY_TABLE => 'oo_plugin_picturetypes',
			self::KEY_FIELD => 'picturetype',
		),
		self::INPUT_TEMPLATE => array(
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'template',
		),
		self::INPUT_LIST_TYPE => array(
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'list_type',
		),
		self::INPUT_SHOW_STATUS => array(
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'show_status',
		),
		self::INPUT_EXPOSE => array(
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'expose',
		),
		self::INPUT_RANDOM_ORDER => array(
			self::KEY_TABLE => 'oo_plugin_listviews',
			self::KEY_FIELD => 'random',
		),
		self::INPUT_FIELD_CONFIG => array(
			self::KEY_TABLE => 'oo_plugin_fieldconfig',
			self::KEY_FIELD => 'fieldname',
		),
	);


	/**
	 *
	 * @param string $type
	 * @param string $label
	 * @param bool $multi
	 * @return \onOffice\WPlugin\Model\InputModelDB
	 *
	 */

	public function create($type, $label, $multi = false)
	{
		$pInstance = null;

		if (array_key_exists($type, $this->_inputConfig))
		{
			$config = $this->_inputConfig[$type];
			$table = $config[self::KEY_TABLE];
			$field = $config[self::KEY_FIELD];

			$pInstance = new \onOffice\WPlugin\Model\InputModelDB(null, $label);
			$pInstance->setTable($table);
			$pInstance->setField($field);
			$pInstance->setIsMulti($multi);
		}

		return $pInstance;
	}
}
