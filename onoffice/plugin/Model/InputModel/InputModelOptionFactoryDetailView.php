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
use onOffice\WPlugin\Model\InputModelOption;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class InputModelOptionFactoryDetailView
{
	/** */
	const INPUT_PICTURE_TYPE = \onOffice\WPlugin\DataView\DataListView::PICTURES;

	/** */
	const INPUT_TEMPLATE = 'template';

	/** */
	const INPUT_EXPOSE = 'expose';

	/** */
	const INPUT_FIELD_CONFIG = \onOffice\WPlugin\DataView\DataListView::FIELDS;

	/** */
	const KEY_TYPE = 'type';

	/** @var string */
	private $_optionGroup = null;


	/** @var array */
	private $_inputConfig = array(
		self::INPUT_EXPOSE => array(
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_STRING,
		),
		self::INPUT_TEMPLATE => array(
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_STRING,
		),
		self::INPUT_PICTURE_TYPE => array(
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_STRING,
		),
		self::INPUT_FIELD_CONFIG => array(
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_STRING,
		),
	);


	/**
	 *
	 * @param string $optionGroup
	 *
	 */

	public function __construct($optionGroup)
	{
		$this->_optionGroup = $optionGroup;
	}


	/**
	 *
	 * @param string $name
	 * @param string $label
	 * @param bool $multi
	 * @return \onOffice\WPlugin\Model\InputModelOption
	 *
	 */

	public function create($name, $label, $multi = false)
	{
		$pInstance = null;

		if (array_key_exists($name, $this->_inputConfig))
		{
			$config = $this->_inputConfig[$name];
			$type = $config[self::KEY_TYPE];

			$pInstance = new \onOffice\WPlugin\Model\InputModelOption($this->_optionGroup, $name, $label, $type);
			$pInstance->setIsMulti($multi);
		}

		return $pInstance;
	}
}
