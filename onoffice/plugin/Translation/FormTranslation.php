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

namespace onOffice\WPlugin\Translation;

use onOffice\WPlugin\Form;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class FormTranslation
{
	/** */
	const SUB_LABEL = 'label';

	/** */
	const SUB_DB_VALUE = 'dbValue';

	/** @var array */
	private $_formConfig = null;


	/**
	 *
	 */

	public function __construct()
	{
		$this->_formConfig = array(
			'all' => array(
				self::SUB_LABEL => _nx_noop('All', 'All', 'forms', 'onoffice'),
				self::SUB_DB_VALUE => null,
			),
			Form::TYPE_CONTACT => array(
				self::SUB_LABEL => _nx_noop('Contact Form', 'Contact Forms', 'forms', 'onoffice'),
				self::SUB_DB_VALUE => Form::TYPE_CONTACT,
			),
			Form::TYPE_INTEREST => array(
				self::SUB_LABEL => _nx_noop('Interest Form', 'Interest Forms', 'forms', 'onoffice'),
				self::SUB_DB_VALUE => Form::TYPE_INTEREST,
			),
			Form::TYPE_OWNER => array(
				self::SUB_LABEL => _nx_noop('Owner Form', 'Owner Forms', 'forms', 'onoffice'),
				self::SUB_DB_VALUE => Form::TYPE_OWNER,
			),
			Form::TYPE_APPLICANT_SEARCH => array(
				self::SUB_LABEL => _nx_noop('Applicant Search Form', 'Applicant Search Forms', 'forms', 'onoffice'),
				self::SUB_DB_VALUE => Form::TYPE_APPLICANT_SEARCH,
			),
		);

	}


	/**
	 *
	 * @param string $formType
	 * @param int $count
	 * @return string
	 *
	 */

	public function getPluralTranslationForForm($formType, $count)
	{
		$formConfig = $this->getFormConfig();
		$label = $formConfig[$formType];

		return translate_nooped_plural($label[self::SUB_LABEL], $count, 'onoffice');
	}

	/** @return array */
	public function getFormConfig()
		{ return $this->_formConfig; }
}
