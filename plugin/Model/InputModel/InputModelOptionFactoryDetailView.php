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

use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataViewSimilarEstates;
use onOffice\WPlugin\Model\ExceptionInputModelMissingField;
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
	const INPUT_PICTURE_TYPE = DataDetailView::PICTURES;

	/** */
	const INPUT_TEMPLATE = 'template';

	/** */
	const INPUT_SHORT_CODE_FORM = 'shortcodeform';

	/** */
	const INPUT_EXPOSE = 'expose';

	/** */
	const INPUT_ACCESS_CONTROL = 'access-control';

	/** */
	const INPUT_RESTRICT_ACCESS_CONTROL = 'restrict-access-control';

	/** */
	const INPUT_MOVIE_LINKS = 'movielinks';

	/** */
	const INPUT_OGULO_LINKS = 'ogulolinks';

	/** */
	const INPUT_OBJECT_LINKS = 'objectlinks';

	/** */
	const INPUT_LINKS = 'links';

	/** */
	const INPUT_FIELD_CONFIG = DataDetailView::FIELDS;

	/** */
	const INPUT_FIELD_CONTACTDATA_ONLY = DataDetailView::ADDRESSFIELDS;

	/** */
	const KEY_TYPE = 'type';

	/** @var string */
	const INPUT_SHOW_STATUS = 'show_status';

	/** @var string */
	const INPUT_SHOW_PRICE_ON_REQUEST = 'show_price_on_request';

	/** */
	const INPUT_CONTACT_IMAGE_TYPES = 'contact_image_types';

	/** @var string */
	private $_optionGroup = null;


	/** @var array */
	private $_inputConfig = [
		self::INPUT_EXPOSE => [
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_STRING,
		],
		self::INPUT_TEMPLATE => [
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_STRING,
		],
		self::INPUT_SHORT_CODE_FORM => [
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_STRING,
		],
		self::INPUT_PICTURE_TYPE => [
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_STRING,
		],
		self::INPUT_FIELD_CONFIG => [
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_STRING,
		],
		self::INPUT_FIELD_CONTACTDATA_ONLY => [
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_STRING,
		],
		self::INPUT_MOVIE_LINKS => [
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_STRING,
		],
		self::INPUT_ACCESS_CONTROL => [
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_STRING,
		],
		self::INPUT_RESTRICT_ACCESS_CONTROL => [
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_STRING,
		],
		self::INPUT_SHOW_STATUS => [
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_BOOLEAN
		],
		self::INPUT_OGULO_LINKS => [
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_STRING
		],
		self::INPUT_OBJECT_LINKS => [
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_STRING
		],
		self::INPUT_LINKS => [
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_STRING
		],
		self::INPUT_SHOW_PRICE_ON_REQUEST => [
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_BOOLEAN
		],
		self::INPUT_CONTACT_IMAGE_TYPES => [
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_STRING
		]
	];


	/**
	 *
	 * @param string $optionGroup
	 *
	 */

	public function __construct(string $optionGroup)
	{
		$this->_optionGroup = $optionGroup;
	}


	/**
	 *
	 * @param string $name
	 * @param string $label
	 * @param bool $multi
	 * @return InputModelOption
	 * @throws ExceptionInputModelMissingField
	 *
	 */

	public function create(string $name, $label, bool $multi = false): InputModelOption
	{
		if (!isset($this->_inputConfig[$name])) {
			throw new ExceptionInputModelMissingField($name);
		}

		$config = $this->_inputConfig[$name];
		$type = $config[self::KEY_TYPE];

		$pInstance = new InputModelOption($this->_optionGroup, $name, $label, $type);
		$pInstance->setIsMulti($multi);

		return $pInstance;
	}
}