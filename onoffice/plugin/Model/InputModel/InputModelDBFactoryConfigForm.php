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

namespace onOffice\WPlugin\Model\InputModel;


/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class InputModelDBFactoryConfigForm
	implements InputModelDBFactoryConfigBase
{
	/** */
	const INPUT_FORM_NAME = 'formName';

	/** */
	const INPUT_FORM_TYPE = 'formType';

	/** */
	const INPUT_FORM_RECIPIENT = 'formRecipient';

	/** */
	const INPUT_FORM_SUBJECT = 'formSubject';

	/** */
	const INPUT_FORM_CREATEADDRESS = 'formCreateAddress';

	/** */
	const INPUT_FORM_CHECKDUPLICATES = 'formCheckDuplicates';

	/** */
	const INPUT_FORM_REQUIRED = 'formRequired';

	/** */
	const INPUT_FORM_LIMIT_RESULTS = 'formLimitResults';

	/** */
	const INPUT_FORM_PAGES = 'formPages';


	/** @var array */
	private $_inputConfig = array(
		self::INPUT_FORM_NAME => array(
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'name',
		),
		InputModelDBFactory::INPUT_TEMPLATE => array(
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'template',
		),
		self::INPUT_FORM_TYPE => array(
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'form_type',
		),
		self::INPUT_FORM_RECIPIENT => array(
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'recipient',
		),
		self::INPUT_FORM_SUBJECT => array(
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'subject',
		),
		self::INPUT_FORM_CREATEADDRESS => array(
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'createaddress',
		),
		self::INPUT_FORM_LIMIT_RESULTS => array(
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'limitresults',
		),
		self::INPUT_FORM_CHECKDUPLICATES => array(
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'checkduplicates',
		),
		self::INPUT_FORM_PAGES => array(
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'pages',
		),
		InputModelDBFactory::INPUT_FIELD_CONFIG => array(
			self::KEY_TABLE => 'oo_plugin_form_fieldconfig',
			self::KEY_FIELD => 'fieldname',
		),
		self::INPUT_FORM_REQUIRED => array(
			self::KEY_TABLE => 'oo_plugin_form_fieldconfig',
			self::KEY_FIELD => 'required',
		),
	);


	/**
	 *
	 * @return array
	 *
	 */

	public function getConfig()
	{
		return $this->_inputConfig;
	}
}
