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

use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigBase;
use onOffice\WPlugin\Model\InputModelDB;

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
	const INPUT_SHOW_REFERENCE_ESTATE = 'showReferenceEstate';

	/** */
	const INPUT_RANDOM_ORDER = 'randomOrder';

	/** */
	const INPUT_EXPOSE = 'expose';

	/** */
	const INPUT_SORT_BY_SETTING = 'sortBySetting';

	/** */
	const INPUT_SORT_BY_CHOSEN = 'sortbyuservalue';

	/** */
	const INPUT_SORT_BY_DEFAULT = 'sortByDefault';

	/** */
	const INPUT_SORT_BY_USER_DEFINED_DIRECTION = 'sortByUserDefinedDirection';

	/** */
	const INPUT_FIELD_CONFIG = 'fieldConfig';


	/** @var InputModelDBFactoryConfigBase */
	private $_pInputModelDBFactoryConfig = null;


	/**
	 *
	 * @param InputModelDBFactoryConfigBase $pInputModelDBFactoryConfig
	 *
	 */

	public function __construct(InputModelDBFactoryConfigBase $pInputModelDBFactoryConfig)
	{
		$this->_pInputModelDBFactoryConfig = $pInputModelDBFactoryConfig;
	}


	/**
	 *
	 * @param string $type
	 * @param string $label
	 * @param bool $multi
	 * @return InputModelDB
	 *
	 */

	public function create($type, $label, $multi = false)
	{
		$pInstance = null;
		$inputConfig = $this->_pInputModelDBFactoryConfig->getConfig();

		if (isset($inputConfig[$type]))
		{
			$config = $inputConfig[$type];
			$table = $config[InputModelDBFactoryConfigBase::KEY_TABLE];
			$field = $config[InputModelDBFactoryConfigBase::KEY_FIELD];

			$pInstance = new InputModelDB(null, $label);
			$pInstance->setTable($table);
			$pInstance->setField($field);
			$pInstance->setIsMulti($multi);
		}

		return $pInstance;
	}


	/** @return InputModelDBFactoryConfigBase */
	public function getInputModelDBFactoryConfig(): InputModelDBFactoryConfigBase
		{ return $this->_pInputModelDBFactoryConfig; }
}
