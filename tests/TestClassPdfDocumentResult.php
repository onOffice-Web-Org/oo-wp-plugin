<?php

/**
 *
 *    Copyright (C) 2020 onOffice GmbH
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

use onOffice\WPlugin\PDF\PdfDocumentResult;
use WP_UnitTestCase;

/**
 *
 */

class TestClassPdfDocumentResult
	extends WP_UnitTestCase
{
	public function testConstruct()
	{
		$content = ['chunk1', "chunk\0\n", 'chunk3'];
		$length = strlen(implode('', $content));
		$pIterator = new \ArrayIterator($content);
		$pPdfDocumentResult = new PdfDocumentResult('text/plain', $length, $pIterator);
		$this->assertEquals('text/plain', $pPdfDocumentResult->getContentType());
		$this->assertEquals($length, $pPdfDocumentResult->getContentLength());
		$this->assertInstanceOf(\Iterator::class, $pPdfDocumentResult->getIterator());
		$this->assertEquals($content, iterator_to_array($pPdfDocumentResult->getIterator()));
	}
}
