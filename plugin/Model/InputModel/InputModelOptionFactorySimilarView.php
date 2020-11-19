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
use onOffice\WPlugin\DataView\DataSimilarView;
use onOffice\WPlugin\DataView\DataViewSimilarEstates;
use onOffice\WPlugin\Model\ExceptionInputModelMissingField;
use onOffice\WPlugin\Model\InputModelOption;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class InputModelOptionFactorySimilarView
{

	/** */
	const INPUT_TEMPLATE = 'template';

    /** */
    const INPUT_FIELD_CONFIG = DataSimilarView::FIELDS;

	/** */
	const INPUT_FIELD_ENABLE_SIMILAR_ESTATES = DataSimilarView::ENABLE_SIMILAR_ESTATES;

	/** */
	const INPUT_FIELD_SIMILAR_ESTATES_SAME_KIND = DataViewSimilarEstates::FIELD_SAME_KIND;

	/** */
	const INPUT_FIELD_SIMILAR_ESTATES_SAME_MARKETING_METHOD = DataViewSimilarEstates::FIELD_SAME_MARKETING_METHOD;

	/** */
	const INPUT_FIELD_SIMILAR_ESTATES_SAME_POSTAL_CODE = DataViewSimilarEstates::FIELD_SAME_POSTAL_CODE;

    /** */
    const INPUT_FIELD_SIMILAR_ESTATES_DONT_SHOW_ARCHIVED = DataViewSimilarEstates::FIELD_DONT_SHOW_ARCHIVED;

    /** */
    const INPUT_FIELD_SIMILAR_ESTATES_DONT_SHOW_REFERENCE = DataViewSimilarEstates::FIELD_DONT_SHOW_REFERENCE;

	/** */
	const INPUT_FIELD_SIMILAR_ESTATES_RADIUS = DataViewSimilarEstates::FIELD_RADIUS;

	/** */
	const INPUT_FIELD_SIMILAR_ESTATES_AMOUNT = DataViewSimilarEstates::FIELD_AMOUNT;

	/** */
	const INPUT_FIELD_SIMILAR_ESTATES_TEMPLATE = DataViewSimilarEstates::FIELD_SIMILAR_ESTATES_TEMPLATE;

	/** */
	const KEY_TYPE = 'type';

	/** @var string */
	private $_optionGroup = null;


	/** @var array */
	private $_inputConfig = [
        self::INPUT_FIELD_CONFIG => [
            self::KEY_TYPE => InputModelOption::SETTING_TYPE_STRING,
        ],
		self::INPUT_FIELD_SIMILAR_ESTATES_SAME_KIND => [
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_BOOLEAN,
		],
		self::INPUT_FIELD_SIMILAR_ESTATES_SAME_MARKETING_METHOD => [
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_BOOLEAN,
		],
		self::INPUT_FIELD_SIMILAR_ESTATES_SAME_POSTAL_CODE => [
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_BOOLEAN,
		],
        self::INPUT_FIELD_SIMILAR_ESTATES_DONT_SHOW_ARCHIVED => [
            self::KEY_TYPE => InputModelOption::SETTING_TYPE_BOOLEAN,
        ],
        self::INPUT_FIELD_SIMILAR_ESTATES_DONT_SHOW_REFERENCE => [
            self::KEY_TYPE => InputModelOption::SETTING_TYPE_BOOLEAN,
        ],
		self::INPUT_FIELD_SIMILAR_ESTATES_RADIUS => [
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_INTEGER,
		],
		self::INPUT_FIELD_SIMILAR_ESTATES_AMOUNT => [
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_INTEGER,
		],
		self::INPUT_FIELD_SIMILAR_ESTATES_TEMPLATE => [
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_STRING,
		],
		self::INPUT_FIELD_ENABLE_SIMILAR_ESTATES => [
			self::KEY_TYPE => InputModelOption::SETTING_TYPE_BOOLEAN,
		],
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
