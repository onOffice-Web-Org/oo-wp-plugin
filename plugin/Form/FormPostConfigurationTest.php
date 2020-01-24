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

use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\CompoundFieldsFilter;
use onOffice\WPlugin\Utility\Logger;
use onOffice\WPlugin\WP\WPOptionWrapperBase;
use onOffice\WPlugin\WP\WPOptionWrapperTest;


/**
 *
 */

class FormPostConfigurationTest
	implements FormPostConfiguration
{
	/** @var string */
	private $_postvarCaptchaToken = '';

	/** @var Logger */
	private $_pLogger;

	/** @var WPOptionWrapperTest */
	private $_pWPOptionsWrapper;

	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort;

	/** @var CompoundFieldsFilter */
	private $_pCompoundFields;

	/**
	 *
	 * @param Logger $pLogger
	 * @param CompoundFieldsFilter $pCompoundFields
	 * @param FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort
	 */

	public function __construct(
		Logger $pLogger,
		CompoundFieldsFilter $pCompoundFields,
		FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort)
	{
		$this->_pWPOptionsWrapper = new WPOptionWrapperTest;
		$this->_pLogger = $pLogger;
		$this->_pCompoundFields = $pCompoundFields;
		$this->_pFieldsCollectionBuilderShort = $pFieldsCollectionBuilderShort;
	}


	/**
	 *
	 * @param string $postvarCaptchaToken
	 *
	 */

	public function setPostvarCaptchaToken(string $postvarCaptchaToken)
	{
		$this->_postvarCaptchaToken = $postvarCaptchaToken;
	}


	/**
	 *
	 * @return Logger
	 *
	 */

	public function getLogger(): Logger
	{
		return $this->_pLogger;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getPostvarCaptchaToken(): string
	{
		return $this->_postvarCaptchaToken;
	}


	/**
	 *
	 * @return WPOptionWrapperBase
	 *
	 */

	public function getWPOptionsWrapper(): WPOptionWrapperBase
	{
		return $this->_pWPOptionsWrapper;
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


	/**
	 *
	 * @return FieldsCollectionBuilderShort
	 *
	 */

	public function getFieldsCollectionBuilderShort(): FieldsCollectionBuilderShort
	{
		return $this->_pFieldsCollectionBuilderShort;
	}
}