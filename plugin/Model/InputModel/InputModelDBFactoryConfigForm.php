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
	const INPUT_FORM_DEFAULT_RECIPIENT = 'formDefaultRecipient';

	/** */
	const INPUT_FORM_SUBJECT = 'formSubject';

	/** */
	const INPUT_FORM_CREATEADDRESS = 'formCreateAddress';

	/** */
	const INPUT_FORM_CREATEINTEREST = 'formCreateInterest';

	/** */
	const INPUT_FORM_CREATEOWNER = 'formCreateOwner';

	/** */
	const INPUT_FORM_CHECKDUPLICATES = 'formCheckDuplicates';

	/** */
	const INPUT_FORM_CHECKDUPLICATES_INTEREST_OWNER = 'formCheckDuplicatesInterestOwner';

	/** */
	const INPUT_FORM_REQUIRES_CAPTCHA = 'formRequiresCaptcha';

	/** */
	const INPUT_FORM_REQUIRED = 'formRequired';

	/** */
	const INPUT_FORM_AVAILABLE_OPTIONS = 'formAvailableOptions';

	/** */
	const INPUT_FORM_LIMIT_RESULTS = 'formLimitResults';

	/** */
	const INPUT_FORM_PAGES = 'formPages';

	/** */
	const INPUT_FORM_MODULE = 'formModule';

	/** */
	const INPUT_FORM_NEWSLETTER = 'newsletter';

	/** */
	const INPUT_FORM_DEFAULT_VALUE = 'defaultValue';

	/** */
	const INPUT_FORM_CUSTOM_LABEL = 'customlabel';

	/** */
	const INPUT_FORM_MARK_DOWN = 'formMarkdown';

	/** */
	const INPUT_FORM_HIDDEN_FIELD = 'formHiddenField';

	/** */
	const INPUT_FORM_ESTATE_CONTEXT_AS_HEADING = 'show_estate_context';

    /** */
    const INPUT_FORM_CONTACT_TYPE = 'contactType';

	/** */
	const INPUT_FORM_WRITE_ACTIVITY = 'writeActivity';

	/** */
	const INPUT_FORM_ACTION_KIND = 'actionKind';

	/** */
	const INPUT_FORM_ACTION_TYPE = 'actionType';

	/** */
	const INPUT_FORM_CHARACTERISTIC = 'characteristic';

	/** */
	const INPUT_FORM_REMARK = 'remark';

	/** */
	const INPUT_FORM_ORIGIN_CONTACT = 'originContact';

	/** */
	const INPUT_FORM_ADVISORY_LEVEL = 'advisoryLevel';

	/** */
	const INPUT_FORM_PAGE_PER_FORM = 'formPagePerForm';

	/** */
	const INPUT_FORM_SHOW_FORM_AS_MODAL = 'showFormAsModal';

	/** */
	const INPUT_FORM_ENABLE_CREATE_TASK = 'enableCreateTask';

	/** */
	const INPUT_FORM_TASK_RESPONSIBILITY = 'responsibility';

	/** */
	const INPUT_FORM_TASK_PROCESSOR = 'processor';

	/** */
	const INPUT_FORM_TASK_TYPE = 'type';

	/** */
	const INPUT_FORM_TASK_PRIORITY = 'priority';

	/** */
	const INPUT_FORM_TASK_SUBJECT = 'subject';

	/** */
	const INPUT_FORM_TASK_DESCRIPTION = 'description';

	/** */
	const INPUT_FORM_TASK_STATUS = 'status';

	/** */
	const TASK_HIGHEST_PRIORITY = 1;

	/** */
	const TASK_HIGH_PRIORITY = 2;

	/** */
	const TASK_NORMAL_PRIORITY = 3;

	/** */
	const TASK_LOW_PRIORITY = 4;

	/** */
	const TASK_LOWEST_PRIORITY = 5;

	/** */
	const TASK_STATUS_NOT_START = 1;

	/** */
	const TASK_STATUS_IN_PROCESS = 2;

	/** */
	const TASK_STATUS_COMPLETED = 3;

	/** */
	const TASK_STATUS_DEFERRED = 4;

	/** */
	const TASK_STATUS_CANCELLED = 5;

	/** */
	const TASK_STATUS_MISCELLANEOUS = 6;

	/** */
	const TASK_STATUS_CHECKED = 7;

	/** */
	const TASK_STATUS_NEED_CLARIFICATION = 8;

	/** @var array */
	private $_inputConfig = [
		self::INPUT_FORM_NAME => [
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'name',
		],
		InputModelDBFactory::INPUT_TEMPLATE => [
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'template',
		],
		self::INPUT_FORM_TYPE => [
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'form_type',
		],
		self::INPUT_FORM_RECIPIENT => [
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'recipient',
		],
		self::INPUT_FORM_DEFAULT_RECIPIENT => [
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'default_recipient',
		],
		self::INPUT_FORM_SUBJECT => [
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'subject',
		],
		self::INPUT_FORM_CREATEADDRESS => [
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'createaddress',
		],
		self::INPUT_FORM_CREATEINTEREST => [
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'createaddress',
		],
		self::INPUT_FORM_CREATEOWNER => [
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'createaddress',
		],
		self::INPUT_FORM_LIMIT_RESULTS => [
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'limitresults',
		],
		self::INPUT_FORM_CHECKDUPLICATES => [
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'checkduplicates',
		],
		self::INPUT_FORM_CHECKDUPLICATES_INTEREST_OWNER => [
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'checkduplicates',
		],
		self::INPUT_FORM_REQUIRES_CAPTCHA => [
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'captcha',
		],
		self::INPUT_FORM_PAGES => [
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'pages',
		],
		self::INPUT_FORM_NEWSLETTER => [
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'newsletter',
		],
		self::INPUT_FORM_ESTATE_CONTEXT_AS_HEADING => [
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'show_estate_context',
		],
		self::INPUT_FORM_CONTACT_TYPE => [
			self::KEY_TABLE => 'oo_plugin_contacttypes',
			self::KEY_FIELD => 'contact_type',
		],

		InputModelDBFactory::INPUT_FIELD_CONFIG => [
			self::KEY_TABLE => 'oo_plugin_form_fieldconfig',
			self::KEY_FIELD => 'fieldname',
		],
		self::INPUT_FORM_REQUIRED => [
			self::KEY_TABLE => 'oo_plugin_form_fieldconfig',
			self::KEY_FIELD => 'required',
		],
		self::INPUT_FORM_MODULE => [
			self::KEY_TABLE => 'oo_plugin_form_fieldconfig',
			self::KEY_FIELD => 'module',
		],
		self::INPUT_FORM_AVAILABLE_OPTIONS => [
			self::KEY_TABLE => 'oo_plugin_form_fieldconfig',
			self::KEY_FIELD => 'availableOptions',
		],
		self::INPUT_FORM_DEFAULT_VALUE => [
			self::KEY_TABLE => 'oo_plugin_fieldconfig_form_defaults_values',
			self::KEY_FIELD => 'value',
		],
		self::INPUT_FORM_CUSTOM_LABEL => [
			self::KEY_TABLE => 'oo_plugin_fieldconfig_form_translated_labels',
			self::KEY_FIELD => 'value',
		],
		self::INPUT_FORM_MARK_DOWN => [
			self::KEY_TABLE => 'oo_plugin_form_fieldconfig',
			self::KEY_FIELD => 'markdown',
		],
		self::INPUT_FORM_HIDDEN_FIELD => [
			self::KEY_TABLE => 'oo_plugin_form_fieldconfig',
			self::KEY_FIELD => 'hidden_field',
		],
		self::INPUT_FORM_WRITE_ACTIVITY => [
			self::KEY_TABLE => 'oo_plugin_form_activityconfig',
			self::KEY_FIELD => 'write_activity',
		],
		self::INPUT_FORM_ACTION_KIND => [
			self::KEY_TABLE => 'oo_plugin_form_activityconfig',
			self::KEY_FIELD => 'action_kind',
		],
		self::INPUT_FORM_ACTION_TYPE => [
			self::KEY_TABLE => 'oo_plugin_form_activityconfig',
			self::KEY_FIELD => 'action_type',
		],
		self::INPUT_FORM_CHARACTERISTIC => [
			self::KEY_TABLE => 'oo_plugin_form_activityconfig',
			self::KEY_FIELD => 'characteristic',
		],
		self::INPUT_FORM_REMARK => [
			self::KEY_TABLE => 'oo_plugin_form_activityconfig',
			self::KEY_FIELD => 'remark',
		],
		self::INPUT_FORM_ORIGIN_CONTACT => [
			self::KEY_TABLE => 'oo_plugin_form_activityconfig',
			self::KEY_FIELD => 'origin_contact',
		],
		self::INPUT_FORM_ADVISORY_LEVEL => [
			self::KEY_TABLE => 'oo_plugin_form_activityconfig',
			self::KEY_FIELD => 'advisory_level',
		],
		self::INPUT_FORM_PAGE_PER_FORM=> [
			self::KEY_TABLE => 'oo_plugin_form_fieldconfig',
			self::KEY_FIELD => 'page_per_form',
		],
		self::INPUT_FORM_SHOW_FORM_AS_MODAL => [
			self::KEY_TABLE => 'oo_plugin_forms',
			self::KEY_FIELD => 'show_form_as_modal',
		],
		self::INPUT_FORM_ENABLE_CREATE_TASK => [
			self::KEY_TABLE => 'oo_plugin_form_taskconfig',
			self::KEY_FIELD => 'enable_create_task',
		],
		self::INPUT_FORM_TASK_RESPONSIBILITY => [
			self::KEY_TABLE => 'oo_plugin_form_taskconfig',
			self::KEY_FIELD => 'responsibility',
		],
		self::INPUT_FORM_TASK_PROCESSOR => [
			self::KEY_TABLE => 'oo_plugin_form_taskconfig',
			self::KEY_FIELD => 'processor',
		],
		self::INPUT_FORM_TASK_TYPE => [
			self::KEY_TABLE => 'oo_plugin_form_taskconfig',
			self::KEY_FIELD => 'type',
		],
		self::INPUT_FORM_TASK_PRIORITY => [
			self::KEY_TABLE => 'oo_plugin_form_taskconfig',
			self::KEY_FIELD => 'priority',
		],
		self::INPUT_FORM_TASK_SUBJECT => [
			self::KEY_TABLE => 'oo_plugin_form_taskconfig',
			self::KEY_FIELD => 'subject',
		],
		self::INPUT_FORM_TASK_DESCRIPTION => [
			self::KEY_TABLE => 'oo_plugin_form_taskconfig',
			self::KEY_FIELD => 'description',
		],
		self::INPUT_FORM_TASK_STATUS => [
			self::KEY_TABLE => 'oo_plugin_form_taskconfig',
			self::KEY_FIELD => 'status',
		],
	];


	/**
	 *
	 * @return array
	 *
	 */

	public function getConfig(): array
	{
		return $this->_inputConfig;
	}
}
