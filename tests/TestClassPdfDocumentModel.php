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

use onOffice\WPlugin\PDF\PdfDocumentModel;
use WP_UnitTestCase;

/**
 *
 */

class TestClassPdfDocumentModel
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testDefaults()
	{
		$pPdfDocumentModel = new PdfDocumentModel(1, 'testView');
		$this->assertEquals(1, $pPdfDocumentModel->getEstateId());
		$this->assertEmpty($pPdfDocumentModel->getTemplate());
		$this->assertEquals('testView', $pPdfDocumentModel->getViewName());
		$this->assertEquals('ENG', $pPdfDocumentModel->getLanguage());
	}


	/**
	 *
	 */

	public function testLanguage()
	{
		$pPdfDocumentModel = new PdfDocumentModel(13, 'abc');
		$pPdfDocumentModel->setLanguage('FRA');
		$this->assertEquals(13, $pPdfDocumentModel->getEstateId());
		$this->assertEmpty($pPdfDocumentModel->getTemplate());
		$this->assertEquals('abc', $pPdfDocumentModel->getViewName());
		$this->assertEquals('FRA', $pPdfDocumentModel->getLanguage());
	}

	/**
	 *
	 */

	public function testTemplate()
	{
		$pPdfDocumentModel = new PdfDocumentModel(13, 'abc');
		$pPdfDocumentModel->setTemplate('def');
		$this->assertEquals(13, $pPdfDocumentModel->getEstateId());
		$this->assertEquals('def', $pPdfDocumentModel->getTemplate());
		$this->assertEquals('abc', $pPdfDocumentModel->getViewName());
		$this->assertEquals('ENG', $pPdfDocumentModel->getLanguage());
	}
}