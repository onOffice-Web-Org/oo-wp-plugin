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

namespace onOffice\tests;

use onOffice\WPlugin\Template;


/**
 *
 */

class TemplateMocker
	extends Template
{
	/** @var string */
	private $_dir = null;


	/**
	 *
	 * @param string $templateName
	 * @param string $dir
	 *
	 */

	public function __construct(string $templateName, string $dir = null)
	{
		parent::__construct($templateName);

		if ($dir != null) {
			$this->_dir = $dir;
		} else {
			$this->_dir = realpath(__DIR__);
		}
	}


	/**
	 *
	 * @return string
	 *
	 */

	protected function buildFilePath(): string
	{
		return $this->_dir.'/'.$this->getTemplateName();
	}
}
