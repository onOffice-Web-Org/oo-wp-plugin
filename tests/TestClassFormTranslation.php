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

use onOffice\WPlugin\Form;
use onOffice\WPlugin\Translation\FormTranslation;

/**
 *
 */

class TestClassFormTranslation
	extends WP_UnitTest_Localized
{
	/** @var FormTranslation */
	private $_pFormTranslation = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pFormTranslation = new FormTranslation();
	}


	/**
	 *
	 * @dataProvider dataProviderUS
	 *
	 * @param string $formType
	 * @param int $count
	 * @param string $expectedWord
	 *
	 */

	public function testGetPluralTranslationForFormUS(string $formType, int $count, string $expectedWord)
	{
		$this->switchLocale('en_US');
		$this->checkGetPluralTranslationForForm($formType, $count, $expectedWord);
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function dataProviderUS(): array
	{
		return [
			[Form::TYPE_APPLICANT_SEARCH, 0, 'Applicant Search Forms'],
			[Form::TYPE_APPLICANT_SEARCH, 1, 'Applicant Search Form'],
			[Form::TYPE_APPLICANT_SEARCH, 2, 'Applicant Search Forms'],

			[Form::TYPE_CONTACT, 0, 'Contact Forms'],
			[Form::TYPE_CONTACT, 1, 'Contact Form'],
			[Form::TYPE_CONTACT, 2, 'Contact Forms'],

			[Form::TYPE_INTEREST, 0, 'Interest Forms'],
			[Form::TYPE_INTEREST, 1, 'Interest Form'],
			[Form::TYPE_INTEREST, 2, 'Interest Forms'],

			[Form::TYPE_OWNER, 0, 'Owner Forms'],
			[Form::TYPE_OWNER, 1, 'Owner Form'],
			[Form::TYPE_OWNER, 2, 'Owner Forms'],
		];
	}


	/**
	 *
	 * @dataProvider dataProviderDE
	 *
	 * @param string $formType
	 * @param int $count
	 * @param string $expectedWord
	 *
	 */

	public function testGetPluralTranslationForFormDE(string $formType, int $count, string $expectedWord)
	{
		$this->switchLocale('de_DE');
		$this->checkGetPluralTranslationForForm($formType, $count, $expectedWord);
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function dataProviderDE(): array
	{
		return [
			[Form::TYPE_APPLICANT_SEARCH, 0, 'Interessentensuchformulare'],
			[Form::TYPE_APPLICANT_SEARCH, 1, 'Interessentensuchformular'],
			[Form::TYPE_APPLICANT_SEARCH, 2, 'Interessentensuchformulare'],

			[Form::TYPE_CONTACT, 0, 'Kontaktformulare'],
			[Form::TYPE_CONTACT, 1, 'Kontaktformular'],
			[Form::TYPE_CONTACT, 2, 'Kontaktformulare'],

			[Form::TYPE_INTEREST, 0, 'Interessentenformulare'],
			[Form::TYPE_INTEREST, 1, 'Interessentenformular'],
			[Form::TYPE_INTEREST, 2, 'Interessentenformulare'],

			[Form::TYPE_OWNER, 0, 'Eigentümerformulare'],
			[Form::TYPE_OWNER, 1, 'Eigentümerformular'],
			[Form::TYPE_OWNER, 2, 'Eigentümerformulare'],
		];
	}


	/**
	 *
	 * @param string $formType
	 * @param int $count
	 * @param string $expectedWord
	 *
	 */

	private function checkGetPluralTranslationForForm(string $formType, int $count, string $expectedWord)
	{
		$this->assertSame($expectedWord,
			$this->_pFormTranslation->getPluralTranslationForForm($formType, $count));
	}


	/**
	 *
	 */

	public function testGetFormConfig()
	{
		$this->assertCount(5, $this->_pFormTranslation->getFormConfig());
	}
}
