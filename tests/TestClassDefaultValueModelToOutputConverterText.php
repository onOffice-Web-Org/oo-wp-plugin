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

use Generator;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelText;
use onOffice\WPlugin\Field\DefaultValue\ModelToOutputConverter\DefaultValueModelToOutputConverterText;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\Types\Field;
use WP_UnitTestCase;


/**
 *
 */

class TestClassDefaultValueModelToOutputConverterText
	extends WP_UnitTestCase
{
	/** */
	const EXPECTEDRESULTS = [
		'de_DE' => [
			'native' => 'testDE',
			'es_SP' => 'testSP',
			'en_US' => 'testUS',
		],
		'en_US' => [
			'de_DE' => 'testDE',
			'es_SP' => 'testSP',
			'native' => 'testUS',
		],
	];

	/** @var Language */
	private $_pLanguage = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pLanguage = $this->getMockBuilder(Language::class)->getMock();
	}


	/**
	 *
	 * @dataProvider generateTestData
	 *
	 * @param string $currentLocale
	 * @param DefaultValueModelText $pModel
	 * @param array $expectation
	 *
	 */

	public function testConvert(string $currentLocale, DefaultValueModelText $pModel, array $expectation)
	{
		$this->_pLanguage->expects($this->once())->method('getLocale')->will($this->returnValue($currentLocale));
		$pConverter = new DefaultValueModelToOutputConverterText($this->_pLanguage);
		$result = $pConverter->convertToRow($pModel);
		$this->assertEquals($expectation, $result);
	}


	/**
	 *
	 * @return Generator
	 *
	 */

	public function generateTestData(): Generator
	{
		$pField = new Field('testField', 'testModule');
		$pModel = new DefaultValueModelText(13, $pField);
		$pModel->addValueByLocale('de_DE', 'testDE');
		$pModel->addValueByLocale('es_SP', 'testSP');
		$pModel->addValueByLocale('en_US', 'testUS');

		foreach (self::EXPECTEDRESULTS as $defaultLang => $expectation) {
			yield [$defaultLang, $pModel, $expectation];
		}
	}
}
