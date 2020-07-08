<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

declare (strict_types=1);

namespace onOffice\WPlugin\PDF;

use onOffice\WPlugin\Language;


/**
 *
 */

class PdfDocumentModel
{
	/** @var int */
	private $_estateId;

	/** @var string */
	private $_language;

	/** @var string */
	private $_template = '';

	/** @var string */
	private $_estateIdExternal = '';

	/** @var string */
	private $_viewName;


	/**
	 *
	 * @param int $estateId
	 * @param string $template
	 * @param string $viewName
	 *
	 */

	public function __construct(int $estateId, string $viewName)
	{
		$this->_estateId = $estateId;
		$this->_language = Language::getDefault();
		$this->_viewName = $viewName;
	}


	/**
	 *
	 * @return int
	 *
	 */

	public function getEstateId(): int
	{
		return $this->_estateId;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getLanguage(): string
	{
		return $this->_language;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getTemplate(): string
	{
		return $this->_template;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getViewName(): string
	{
		return $this->_viewName;
	}


	/**
	 *
	 * @param string $language
	 *
	 */

	public function setLanguage(string $language)
	{
		$this->_language = $language;
	}


	/**
	 *
	 * @param string $template
	 *
	 */

	public function setTemplate(string $template)
	{
		$this->_template = $template;
	}

	/**
	 * @return string
	 */
	public function getEstateIdExternal(): string
	{
		return $this->_estateIdExternal;
	}

	/**
	 * @param string $estateIdExternal
	 */
	public function setEstateIdExternal(string $estateIdExternal)
	{
		$this->_estateIdExternal = $estateIdExternal;
	}
}
