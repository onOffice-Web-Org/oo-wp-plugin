<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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

namespace onOffice\WPlugin\DataFormConfiguration;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class DataFormConfiguration
{
	/** */
	const FIELDS = 'fields';


	/** @var string */
	private $_formType = null;

	/** @var string */
	private $_formName = null;

	/** @var array */
	private $_requiredFields = array();

	/** @var string */
	private $_language = '';

	/** @var array */
	private $_inputs = array();

	/** @var string */
	private $_template = '';


	/**
	 *
	 * Override to set default fields for new, empty forms
	 *
	 */

	public function setDefaultFields()
		{}

	/** @return string */
	public function getFormType()
		{ return $this->_formType; }

	/** @return array */
	public function getRequiredFields()
		{ return $this->_requiredFields; }

	/** @return string */
	public function getLanguage()
		{ return $this->_language; }

	/** @return array */
	public function getInputs()
		{ return $this->_inputs; }

	/** @param string $formType */
	public function setFormType($formType)
		{ $this->_formType = $formType; }

	/** @param array $requiredFields */
	public function setRequiredFields(array $requiredFields)
		{ $this->_requiredFields = $requiredFields; }

	/** @param string $language */
	public function setLanguage($language)
		{ $this->_language = $language; }

	/** @param array $inputs */
	public function setInputs(array $inputs)
		{ $this->_inputs = $inputs; }

	/** @param string $requiredField */
	public function addRequiredField($requiredField)
		{ $this->_requiredFields []= $requiredField; }

	/**
	 *
	 * @param string $input
	 * @param string $module null if wp-only input
	 *
	 */

	public function addInput($input, $module = null)
		{ $this->_inputs[$input] = $module; }

	/** @return string */
	public function getTemplate()
		{ return $this->_template; }

	/** @param string $template */
	public function setTemplate($template)
		{ $this->_template = $template; }

	/** @return string */
	public function getFormName()
		{ return $this->_formName; }

	/** @param string $formName */
	public function setFormName($formName)
		{ $this->_formName = $formName; }
}
