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

use Iterator;

/**
 *
 */
class PdfDocumentResult
{
	/** @var string */
	private $_contentType;

	/** @var int */
	private $_contentLength;

	/** @var Iterator */
	private $_pIterator;

	/**
	 * @param string $contentType
	 * @param int $contentLength
	 * @param Iterator $pIterator
	 */
	public function __construct(string $contentType, int $contentLength, Iterator $pIterator)
	{
		$this->_contentType = $contentType;
		$this->_contentLength = $contentLength;
		$this->_pIterator = $pIterator;
	}

	/**
	 * @return string
	 */
	public function getContentType(): string
	{
		return $this->_contentType;
	}

	/**
	 * @return int
	 */
	public function getContentLength(): int
	{
		return $this->_contentLength;
	}

	/**
	 * @return Iterator
	 */
	public function getIterator(): Iterator
	{
		return $this->_pIterator;
	}
}
