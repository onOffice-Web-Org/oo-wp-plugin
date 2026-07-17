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

declare (strict_types=1);

namespace onOffice\WPlugin\Translation;

use onOffice\WPlugin\Form;
use function _nx_noop;
use function translate_nooped_plural;


/**
 *
 */

class FormTranslation
{
	/** */
	const SUB_LABEL = 'label';

	/** */
	const SUB_DB_VALUE = 'dbValue';

	/** @var array */
	private $_formConfig;


	/**
	 *
	 */

	public function __construct()
	{
		$this->_formConfig = [
			'all' => [
				self::SUB_LABEL => _nx_noop('All', 'All', 'forms', 'onoffice-for-wp-websites'),
				self::SUB_DB_VALUE => null,
			],
			Form::TYPE_CONTACT => [
				self::SUB_LABEL => _nx_noop('Contact Form', 'Contact Forms', 'forms', 'onoffice-for-wp-websites'),
				self::SUB_DB_VALUE => Form::TYPE_CONTACT,
			],
			Form::TYPE_INTEREST => [
				self::SUB_LABEL => _nx_noop('Interest Form', 'Interest Forms', 'forms', 'onoffice-for-wp-websites'),
				self::SUB_DB_VALUE => Form::TYPE_INTEREST,
			],
			Form::TYPE_OWNER => [
				self::SUB_LABEL => _nx_noop('Owner Form', 'Owner Forms', 'forms', 'onoffice-for-wp-websites'),
				self::SUB_DB_VALUE => Form::TYPE_OWNER,
			],
			Form::TYPE_APPLICANT_SEARCH => [
				self::SUB_LABEL => _nx_noop('Applicant Search Form', 'Applicant Search Forms', 'forms', 'onoffice-for-wp-websites'),
				self::SUB_DB_VALUE => Form::TYPE_APPLICANT_SEARCH,
			],
		];
	}


	/**
	 *
	 * @param string $formType
	 * @param int $count
	 * @return string
	 *
	 */

	public function getPluralTranslationForForm(string $formType, int $count): string
	{
		$formConfig = $this->getFormConfig();
		$label = $formConfig[$formType] ?? null;

		return translate_nooped_plural($label[self::SUB_LABEL], $count, 'onoffice-for-wp-websites') ?? '';
	}

	/** @return array */
	public function getFormConfig(): array
		{ return $this->_formConfig; }
}
