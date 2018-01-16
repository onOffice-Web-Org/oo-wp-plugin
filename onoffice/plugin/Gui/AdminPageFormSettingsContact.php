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

namespace onOffice\WPlugin\Gui;

use onOffice\WPlugin\Model;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderForm;

/**
 *
 */

class AdminPageFormSettingsContact
	extends AdminPageFormSettingsBase
{
	/** @var bool */
	private $_showSearchCriteriaBoxes = false;

	/** @var bool */
	private $_showPagesOption = false;

	/** @var bool */
	private $_showEstateFields = false;

	/** @var bool */
	private $_showAddressFields = false;

	/** @var bool message field has no module */
	private $_showMessageInput = false;

	/** @var array */
	private $_additionalCategories = array();

	/**
	 *
	 */

	protected function buildForms()
	{
		add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );
		$pFormModelBuilder = new FormModelBuilderForm($this->getPageSlug());
		$pFormModelBuilder->setFormType($this->getType());
		$pFormModel = $pFormModelBuilder->generate($this->getListViewId());
		$this->addFormModel($pFormModel);

		$pInputModelName = $pFormModelBuilder->createInputModelName();
		$pFormModelName = new Model\FormModel();
		$pFormModelName->setPageSlug($this->getPageSlug());
		$pFormModelName->setGroupSlug(self::FORM_RECORD_NAME);
		$pFormModelName->setLabel(__('choose name', 'onoffice'));
		$pFormModelName->addInputModel($pInputModelName);
		$this->addFormModel($pFormModelName);

		$pInputModelTemplate = $pFormModelBuilder->createInputModelTemplate();
		$pFormModelLayoutDesign = new Model\FormModel();
		$pFormModelLayoutDesign->setPageSlug($this->getPageSlug());
		$pFormModelLayoutDesign->setGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$pFormModelLayoutDesign->setLabel(__('Layout & Design', 'onoffice'));
		$pFormModelLayoutDesign->addInputModel($pInputModelTemplate);
		$this->addFormModel($pFormModelLayoutDesign);

		$pInputModelRecipient = $pFormModelBuilder->createInputModelRecipient();
		$pInputModelCreateAddress = $pFormModelBuilder->createInputModelCreateAddress();
		$pInputModelCheckDuplicates = $pFormModelBuilder->createInputModelCheckDuplicates();
		$pInputModelSubject = $pFormModelBuilder->createInputModelSubject();
		$pFormModelFormSpecific = new Model\FormModel();
		$pFormModelFormSpecific->setPageSlug($this->getPageSlug());
		$pFormModelFormSpecific->setGroupSlug(self::FORM_VIEW_FORM_SPECIFIC);
		$pFormModelFormSpecific->setLabel(__('Form Specific', 'onoffice'));
		$pFormModelFormSpecific->addInputModel($pInputModelRecipient);
		$pFormModelFormSpecific->addInputModel($pInputModelSubject);
		$pFormModelFormSpecific->addInputModel($pInputModelCreateAddress);
		$pFormModelFormSpecific->addInputModel($pInputModelCheckDuplicates);
		if ($this->_showPagesOption) {
			$pInputModelPages = $pFormModelBuilder->createInputModelPages();
			$pFormModelFormSpecific->addInputModel($pInputModelPages);
		}

		$this->addFormModel($pFormModelFormSpecific);

		$sortableFieldListModules = array();

		if ($this->_showEstateFields) {
			$fieldNamesEstate = $this->readFieldnamesByContent(onOfficeSDK::MODULE_ESTATE);
			$this->addFieldsConfiguration
				(onOfficeSDK::MODULE_ESTATE, $pFormModelBuilder, $fieldNamesEstate, true);
			$sortableFieldListModules []= onOfficeSDK::MODULE_ESTATE;
		}

		if ($this->_showAddressFields) {
			$fieldNamesAddress = $this->readFieldnamesByContent(onOfficeSDK::MODULE_ADDRESS);
			$this->addFieldsConfiguration
				(onOfficeSDK::MODULE_ADDRESS, $pFormModelBuilder, $fieldNamesAddress, true);
			$sortableFieldListModules []= onOfficeSDK::MODULE_ADDRESS;
		}

		if ($this->_showSearchCriteriaBoxes) {
			// todo
		}

		if ($this->_showMessageInput) {
			$category = __('Form-Specific', 'onoffice');
			$this->_additionalCategories[] = $category;

			$fieldNameMessage = array($category => array(
					'message' => __('Message', 'onoffice'),
				),
			);
			$this->addFieldsConfiguration(null, $pFormModelBuilder, $fieldNameMessage, true);
			$sortableFieldListModules []= null;
			$pFormModelBuilder->setAdditionalFields(array(
				'message' => array(
					'content' => $category,
					'label' => __('Message', 'onoffice'),
				),
			));
		}

		$this->addSortableFieldsList($sortableFieldListModules, $pFormModelBuilder,
			InputModelBase::HTML_TYPE_COMPLEX_SORTABLE_DETAIL_LIST_FORM);
	}


	/**
	 *
	 */

	protected function generateMetaBoxes()
	{
		$pFormFormSpecific = $this->getFormModelByGroupSlug(self::FORM_VIEW_FORM_SPECIFIC);
		$this->createMetaBoxByForm($pFormFormSpecific, 'normal');

		$pFormLayoutDesign = $this->getFormModelByGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$this->createMetaBoxByForm($pFormLayoutDesign, 'normal');
	}


	/**
	 *
	 */

	protected function generateAccordionBoxes()
	{
		$fieldNames = array();

		if ($this->_showEstateFields) {
			$fieldNames = array_merge($fieldNames,
				array_keys($this->readFieldnamesByContent(onOfficeSDK::MODULE_ESTATE)));
		}

		if ($this->_showAddressFields) {
			$fieldNames = array_merge($fieldNames,
				array_keys($this->readFieldnamesByContent(onOfficeSDK::MODULE_ADDRESS)));
		}

		foreach ($fieldNames as $category) {
			$pFormFieldsConfig = $this->getFormModelByGroupSlug($category);
			$this->createMetaBoxByForm($pFormFieldsConfig, 'side');
		}

		foreach ($this->_additionalCategories as $category) {
			$pFormFieldsConfigCategory = $this->getFormModelByGroupSlug($category);
			$this->createMetaBoxByForm($pFormFieldsConfigCategory, 'side');
		}
	}


	/** @return bool */
	public function getShowSearchCriteriaBoxes()
		{ return $this->_showSearchCriteriaBoxes; }

	/** @param bool $showSearchCriteriaBoxes */
	public function setShowSearchCriteriaBoxes($showSearchCriteriaBoxes)
		{ $this->_showSearchCriteriaBoxes = (bool)$showSearchCriteriaBoxes; }

	/** @return bool */
	public function getShowPagesOption()
		{ return $this->_showPagesOption; }

	/** @param bool $showPagesOption */
	public function setShowPagesOption($showPagesOption)
		{ $this->_showPagesOption = (bool)$showPagesOption; }

	/** @return bool */
	public function getShowEstateFields()
		{ return $this->_showEstateFields; }

	/** @return bool */
	public function getShowAddressFields()
		{ return $this->_showAddressFields; }

	/** @param bool $showEstateFields */
	public function setShowEstateFields($showEstateFields)
		{ $this->_showEstateFields = (bool)$showEstateFields; }

	/** @param bool $showAddressFields */
	public function setShowAddressFields($showAddressFields)
		{ $this->_showAddressFields = (bool)$showAddressFields; }

	/** @return bool */
	public function getShowMessageInput()
		{ return $this->_showMessageInput; }

	/** @param bool $showMessageInput */
	public function setShowMessageInput($showMessageInput)
		{ $this->_showMessageInput = (bool)$showMessageInput; }
}
