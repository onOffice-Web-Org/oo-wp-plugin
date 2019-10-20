<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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

namespace onOffice\WPlugin\Form;

use onOffice\WPlugin\Utility\Logger;
use onOffice\WPlugin\WP\WPOptionWrapperBase;
use onOffice\WPlugin\WP\WPOptionWrapperDefault;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\CompoundFieldsFilter;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class FormPostConfigurationDefault
	implements FormPostConfiguration
{
	/** @var FieldsCollectionBuilderShort*/
	private $_pFieldsCollectionBuilderShort = null;

	/** @var CompoundFieldsFilter */
	private $_pCompoundFields = null;


	/**
	 *
	 * @param FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort
	 * @param CompoundFieldsFilter $pCompoundFields
	 *
	 */

	public function __construct(FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort,
			CompoundFieldsFilter $pCompoundFields)
	{
		$this->_pFieldsCollectionBuilderShort = $pFieldsCollectionBuilderShort;
		$this->_pCompoundFields = $pCompoundFields;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getPostvarCaptchaToken(): string
	{
		return filter_input(INPUT_POST, CaptchaHandler::RECAPTCHA_RESPONSE_PARAM) ?? '';
	}


	/**
	 *
	 * @return Logger
	 *
	 */

	public function getLogger(): Logger
	{
		return new Logger;
	}


	/**
	 *
	 * @return WPOptionWrapperBase
	 *
	 */

	public function getWPOptionsWrapper(): WPOptionWrapperBase
	{
		return new WPOptionWrapperDefault;
	}


	/**
	 *
	 * @return FieldsCollectionBuilderShort
	 *
	 */

	public function getFieldsCollectionBuilderShort(): FieldsCollectionBuilderShort
	{
		return $this->_pFieldsCollectionBuilderShort;
	}


	/**
	 *
	 * @return CompoundFieldsFilter
	 *
	 */

	public function getCompoundFields(): CompoundFieldsFilter
	{
		return $this->_pCompoundFields;
	}
}