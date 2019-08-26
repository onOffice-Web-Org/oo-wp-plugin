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

/**
 *
 */

class PdfDocumentResult
{
	/** @var string */
	private $_mimetype;

	/** @var string */
	private $_binary;


	/**
	 *
	 * @param string $mime
	 * @param string $binary
	 *
	 */

	public function __construct(string $mime, string $binary)
	{
		$this->_mimetype = $mime;
		$this->_binary = $binary;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getMimetype(): string
	{
		return $this->_mimetype;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getBinary(): string
	{
		return $this->_binary;
	}
}
