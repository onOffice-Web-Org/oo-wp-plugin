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
use onOffice\WPlugin\WP\WPOptionWrapperTest;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\CompoundFields;
use DI\Container;


/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class FormPostConfigurationTest
	implements FormPostConfiguration
{
	/** @var array */
	private $_postVariables = [];

	/** @var string */
	private $_postvarCaptchaToken = '';

	/** @var Logger */
	private $_pLogger = null;

	/** @var WPOptionWrapperTest */
	private $_pWPOptionsWrapper = null;

	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort = null;

	/** @var CompoundFields */
	private $_pCompoundFields = null;


	/**
	 *
	 * @param Logger $pLogger
	 *
	 */

	public function __construct(Logger $pLogger)
	{
		$this->_pWPOptionsWrapper = new WPOptionWrapperTest;
		$this->_pLogger = $pLogger;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getPostVars(): array
	{
		return $this->_postVariables;
	}


	/**
	 *
	 * @param array $postVariables
	 *
	 */

	public function setPostVariables(array $postVariables)
	{
		$this->_postVariables = $postVariables;
	}


	/**
	 *
	 * @param string $key
	 * @param string $value
	 *
	 */

	public function addPostVariableString(string $key, string $value)
	{
		$this->_postVariables[$key] = $value;
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
	 * @return CompoundFields
	 *
	 */

	public function getCompoundFields(): CompoundFields
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


	/**
	 *
	 * @param CompoundFields $pCompoundFields
	 *
	 */

	public function setCompoundFields(CompoundFields $pCompoundFields)
	{
		$this->_pCompoundFields = $pCompoundFields;
	}


	/**
	 *
	 * @param FieldsCollectionBuilderShort $pBuilder
	 *
	 */

	public function setFieldsCollectionBuilderShort(FieldsCollectionBuilderShort $pBuilder)
	{
		$this->_pFieldsCollectionBuilderShort = $pBuilder;
	}
}