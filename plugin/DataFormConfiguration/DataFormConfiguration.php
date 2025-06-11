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

use onOffice\WPlugin\Controller\ViewProperty;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class DataFormConfiguration
	implements ViewProperty
{
	/** */
	const FIELDS = 'fields';


	/** @var string */
	private $_formType = '';

	/** @var string */
	private $_formName = '';

	/** @var array */
	private $_requiredFields = [];

	/** @var string */
	private $_language = '';

	/** @var array */
	private $_inputs = [];

	/** @var string */
	private $_template = '';

	/** @var bool */
	private $_captcha = false;

	/** @var int The Form ID */
	private $_id = 0;

	/** @var array */
	private $_availableOptionsFields = [];

	/** @var bool */
	private $_showEstateContext = false;

	/** @var array */
	private $_contactType = [];

	/** @var string */
	private $_recipient = '';

	/** @var bool */
	private $_defaultRecipient = false;

	/** @var array */
	private $_markdownFields = [];

	/** @var array */
	private $_hiddenFields = [];

	/** @var bool */
	private $_writeActivity = false;

	/** @var string */
	private $_actionKind = '';

	/** @var string */
	private $_actionType = '';

	/** @var string */
	private $_characteristic = '';

	/** @var string */
	private $_remark = '';

	/** @var string */
	private $_originContact = '';

	/** @var string */
	private $_advisorylevel = '';

	/** @var array */
	private $_pagePerForm = [];

	/** @var array */
	private $_titlePerMultipage = [];

	/** @var bool */
	private $_enableCreateTask = false;

	/** @var string */
	private $_taskResponsibility = '';

	/** @var string */
	private $_taskProcessor = '';

	/** @var int */
	private $_taskType = 0;

	/** @var string */
	private $_taskSubject = '';

	/** @var string */
	private $_taskDescription = '';

	/** @var int */
	private $_taskStatus = 0;

	/** @var int */
	private $_taskPriority = 0;

	/**
	 *
	 * Override to set default fields for new, empty forms
	 *
	 */

	public function setDefaultFields()
		{}

	/** @return string */
	public function getFormType(): string
		{ return $this->_formType; }

	/** @return array */
	public function getRequiredFields(): array
		{ return $this->_requiredFields; }

	/** @return array */
	public function getAvailableOptionsFields(): array
		{ return $this->_availableOptionsFields; }

	/** @return string */
	public function getLanguage(): string
		{ return $this->_language; }

	/** @return array */
	public function getInputs(): array
		{ return $this->_inputs; }

	/** @return array */
	public function getPagePerForm(): array
		{ return $this->_pagePerForm; }

	/** @return array */
	public function getTitlePerMultipage(): array
		{ return $this->_titlePerMultipage; }

	/**
	 * @param array $titleData
	 */
	public function addTitlePerMultipagePage(array $titleData): void
	{ $this->_titlePerMultipage[] = $titleData; }

	/** @param string $formType */
	public function setFormType(string $formType)
		{ $this->_formType = $formType; }

	/** @param array $requiredFields */
	public function setRequiredFields(array $requiredFields)
		{ $this->_requiredFields = $requiredFields; }

	/** @param array $availableOptionsFields */
	public function setAvailableOptionsFields(array $availableOptionsFields)
		{ $this->_availableOptionsFields = $availableOptionsFields; }

	/** @param string $language */
	public function setLanguage(string $language)
		{ $this->_language = $language; }

	/** @param array $inputs */
	public function setInputs(array $inputs)
		{ $this->_inputs = $inputs; }

	/** @param string $requiredField */
	public function addRequiredField(string $requiredField)
		{ $this->_requiredFields []= $requiredField; }

	/** @param string $availableOptionsField */
	public function addAvailableOptionsField(string $availableOptionsField)
		{ $this->_availableOptionsFields []= $availableOptionsField; }

	/** @param bool $showEstateContext */
	public function setShowEstateContext(bool $showEstateContext)
		{ $this->_showEstateContext = $showEstateContext; }

	/** @param array $contactTypeField */
	public function setContactTypeField(array $contactTypeField)
		{ $this->_contactType = $contactTypeField; }

	/** @return array */
	public function getMarkdownFields(): array
		{ return $this->_markdownFields; }

	/** @param array $markdownFields */
	public function setMarkdownFields(array $markdownFields)
		{ $this->_markdownFields = $markdownFields; }

	/** @return array */
	public function getHiddenFields(): array
	{ return $this->_hiddenFields; }

	/** @param string $hiddenField */
	public function addHiddenFields(string $hiddenField)
	{ $this->_hiddenFields []= $hiddenField; }

	/** @param string $requiredField */
	public function addMarkdownFields(string $markdownFields)
		{ $this->_markdownFields []= $markdownFields; }

	/** 
	 * @param string $fieldName
	 * @param string $pagePerForm 
	 */
	public function addPagePerForm(string $fieldName, string $pagePerForm)
		{ $this->_pagePerForm [$fieldName] = (int) $pagePerForm; }

	/**
	 *
	 * @param string $input
	 * @param string $module null if wp-only input
	 *
	 */

	public function addInput(string $input, $module = null)
		{ $this->_inputs[$input] = $module; }

	/** @return string */
	public function getTemplate(): string
		{ return $this->_template; }

	/** @param string $template */
	public function setTemplate(string $template)
		{ $this->_template = $template; }

	/** @return string */
	public function getFormName(): string
		{ return $this->_formName; }

	/** @param string $formName */
	public function setFormName(string $formName)
		{ $this->_formName = $formName; }

	/** @return bool */
	public function getCaptcha(): bool
		{ return $this->_captcha; }

	/** @param bool $captcha */
	public function setCaptcha(bool $captcha)
		{ $this->_captcha = $captcha; }

	/** @return int */
	public function getId(): int
		{ return $this->_id; }

	/** @return string */
	public function getModule(): string
		{ return 'form'; }

	/** @return string */
	public function getViewType(): string
		{ return $this->_formType; }

	/** @param int $id */
	public function setId(int $id)
		{ $this->_id = $id; }

	/** @return bool */
	public function getShowEstateContext(): bool
		{ return $this->_showEstateContext; }

	/** @return array */
	public function getContactType(): array
		{ return $this->_contactType; }

	/** @return bool */
	public function getDefaultRecipient()
		{ return $this->_defaultRecipient; }

	/** @param bool $defaultRecipient */
	public function setDefaultRecipient(bool $defaultRecipient)
		{ $this->_defaultRecipient = $defaultRecipient; }

	/** @return string */
	public function getRecipient()
		{ return $this->_recipient; }

	/** @param string $recipient */
	public function setRecipient($recipient)
		{ $this->_recipient = $recipient; }

	/**
	 * @return string
	 */
	public function getRecipientByUserSelection(): string {
		if ( $this->_defaultRecipient ) {
			return get_option( 'onoffice-settings-default-email', '' );
		}

		return $this->_recipient;
	}

	/** @return bool */
	public function getWriteActivity(): bool
		{ return $this->_writeActivity; }

	/** @param bool $writeActivity */
	public function setWriteActivity(bool $writeActivity)
		{ $this->_writeActivity = $writeActivity; }

	/** @return string */
	public function getActionKind(): string
		{ return $this->_actionKind; }

	/** @param string $actionKind */
	public function setActionKind(string $actionKind)
		{ $this->_actionKind = $actionKind; }
	
	/** @return string */
	public function getActionType(): string
		{ return $this->_actionType; }

	/** @param string $actionType */
	public function setActionType(string $actionType)
		{ $this->_actionType = $actionType; }

	/** @return string */
	public function getCharacteristic(): string
		{ return $this->_characteristic; }

	/** @param string $characteristic */
	public function setCharacteristic(string $characteristic)
		{ $this->_characteristic = $characteristic; }
	
	/** @return string */
	public function getRemark(): string
		{ return $this->_remark; }

	/** @param string $remark */
	public function setRemark(string $remark)
		{ $this->_remark = $remark; }
	
	/** @return string */
	public function getOriginContact(): string
		{ return $this->_originContact; }

	/** @param string $originContact */
	public function setOriginContact(string $originContact)
		{ $this->_originContact = $originContact; }
	
	/** @return string */
	public function getAdvisorylevel(): string
		{ return $this->_advisorylevel; }

	/** @param string $advisorylevel */
	public function setAdvisorylevel(string $advisorylevel)
		{ $this->_advisorylevel = $advisorylevel; }

	/** @return bool */
	public function getEnableCreateTask(): bool
		{ return $this->_enableCreateTask; }

	/** @param bool $enableCreateTask */
	public function setEnableCreateTask(bool $enableCreateTask)
		{ $this->_enableCreateTask = $enableCreateTask; }

	/** @return string */
	public function getTaskResponsibility(): string
		{ return $this->_taskResponsibility; }

	/** @param string $taskResponsibility */
	public function setTaskResponsibility(string $taskResponsibility)
		{ $this->_taskResponsibility = $taskResponsibility; }

	/** @return string */
	public function getTaskProcessor(): string
		{ return $this->_taskProcessor; }

	/** @param string $taskProcessor */
	public function setTaskProcessor(string $taskProcessor)
		{ $this->_taskProcessor = $taskProcessor; }

	/** @return int */
	public function getTaskType(): int
		{ return $this->_taskType; }

	/** @param int $taskType */
	public function setTaskType(int $taskType)
		{ $this->_taskType = $taskType; }

	/** @return int */
	public function getTaskPriority(): int
		{ return $this->_taskPriority; }

	/** @param int $taskPriority */
	public function setTaskPriority(int $taskPriority)
		{ $this->_taskPriority = $taskPriority; }

	/** @return string */
	public function getTaskSubject(): string
		{ return $this->_taskSubject; }

	/** @param string $taskSubject */
	public function setTaskSubject(string $taskSubject)
		{ $this->_taskSubject = $taskSubject; }

	/** @return string */
	public function getTaskDescription(): string
		{ return $this->_taskDescription; }

	/** @param string $taskDescription */
	public function setTaskDescription(string $taskDescription)
		{ $this->_taskDescription = $taskDescription; }

	/** @return int */
	public function getTaskStatus(): int
		{ return $this->_taskStatus; }

	/** @param int $taskStatus */
	public function setTaskStatus(int $taskStatus)
		{ $this->_taskStatus = $taskStatus; }
}
