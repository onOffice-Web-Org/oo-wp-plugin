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

use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModelBase;

/**
 *
 */

class AdminPageFormSettingsContact
	extends AdminPageFormSettingsBase
{
	/** @var bool */
	private $_showPagesOption = false;

	/** @var bool message field has no module */
	private $_showMessageInput = false;

	/** @var array */
	private $_additionalCategories = array();

	/** @var bool */
	private $_showCreateAddress = false;

	/** @var bool */
	private $_showCheckDuplicates = false;

	/** @var bool */
	private $_showNewsletterCheckbox = false;


	/**
	 *
	 */

	protected function buildForms()
	{
		parent::buildForms();
		$pFormModelBuilder = $this->getFormModelBuilder();

		$pInputModelRecipient = $pFormModelBuilder->createInputModelRecipient();
		$pInputModelSubject = $pFormModelBuilder->createInputModelSubject();
		$pInputModelCaptcha = $pFormModelBuilder->createInputModelCaptchaRequired();
		$pFormModelFormSpecific = new FormModel();
		$pFormModelFormSpecific->setPageSlug($this->getPageSlug());
		$pFormModelFormSpecific->setGroupSlug(self::FORM_VIEW_FORM_SPECIFIC);
		$pFormModelFormSpecific->setLabel(__('Form Specific Options', 'onoffice'));
		$pFormModelFormSpecific->addInputModel($pInputModelRecipient);
		$pFormModelFormSpecific->addInputModel($pInputModelSubject);
		$pFormModelFormSpecific->addInputModel($pInputModelCaptcha);

		if ($this->_showCreateAddress) {
			$pInputModelCreateAddress = $pFormModelBuilder->createInputModelCreateAddress();
			$pFormModelFormSpecific->addInputModel($pInputModelCreateAddress);
		}

		if ($this->_showCheckDuplicates) {
			$pInputModelCheckDuplicates = $pFormModelBuilder->createInputModelCheckDuplicates();
			$pFormModelFormSpecific->addInputModel($pInputModelCheckDuplicates);
		}

		if ($this->_showPagesOption) {
			$pInputModelPages = $pFormModelBuilder->createInputModelPages();
			$pFormModelFormSpecific->addInputModel($pInputModelPages);
		}

		if ($this->_showNewsletterCheckbox) {
			$pInputModelNewsletter = $pFormModelBuilder->createInputModelNewsletterCheckbox();
			$pFormModelFormSpecific->addInputModel($pInputModelNewsletter);
		}

		$this->addFormModel($pFormModelFormSpecific);
		$this->addFieldConfigurationForMainModules($pFormModelBuilder);

		if ($this->_showMessageInput) {
			$category = __('Form Specific Fields', 'onoffice');
			$this->_additionalCategories[] = $category;

			$fieldNameMessage = array($category => array(
					'message' => __('Message', 'onoffice'),
				),
			);
			$this->addFieldsConfiguration(null, $pFormModelBuilder, $fieldNameMessage, true);
			$this->addSortableFieldModule(null);
			$pFormModelBuilder->setAdditionalFields(array(
				'message' => array(
					'content' => $category,
					'label' => __('Message', 'onoffice'),
				),
			));
		}

		$this->addSortableFieldsList($this->getSortableFieldModules(), $pFormModelBuilder,
			InputModelBase::HTML_TYPE_COMPLEX_SORTABLE_DETAIL_LIST);
	}


	/**
	 *
	 */

	protected function generateMetaBoxes()
	{
		$pFormFormSpecific = $this->getFormModelByGroupSlug(self::FORM_VIEW_FORM_SPECIFIC);
		$this->createMetaBoxByForm($pFormFormSpecific, 'side');

		parent::generateMetaBoxes();
	}


	/**
	 *
	 */

	protected function generateAccordionBoxes()
	{
		parent::generateAccordionBoxes();

		foreach ($this->_additionalCategories as $category) {
			$slug = $this->generateGroupSlugByModuleCategory(null, $category);
			$pFormFieldsConfigCategory = $this->getFormModelByGroupSlug($slug);
			$this->createMetaBoxByForm($pFormFieldsConfigCategory, 'side');
		}
	}


	/** @return bool */
	public function getShowPagesOption(): bool
		{ return $this->_showPagesOption; }

	/** @param bool $showPagesOption */
	public function setShowPagesOption(bool $showPagesOption)
		{ $this->_showPagesOption = $showPagesOption; }

	/** @return bool */
	public function getShowMessageInput(): bool
		{ return $this->_showMessageInput; }

	/** @param bool $showMessageInput */
	public function setShowMessageInput(bool $showMessageInput)
		{ $this->_showMessageInput = $showMessageInput; }

	/** @return bool */
	public function getShowCreateAddress(): bool
		{ return $this->_showCreateAddress; }

	/** @return bool */
	public function getShowCheckDuplicates(): bool
		{ return $this->_showCheckDuplicates; }

	/** @param bool $showCreateAddress */
	public function setShowCreateAddress(bool $showCreateAddress)
		{ $this->_showCreateAddress = $showCreateAddress; }

	/** @param bool $showCheckDuplicates */
	public function setShowCheckDuplicates(bool $showCheckDuplicates)
		{ $this->_showCheckDuplicates = $showCheckDuplicates; }

	/** @return bool */
	public function getShowNewsletterCheckbox(): bool
		{ return $this->_showNewsletterCheckbox; }

	/** @param bool $showNewsletterCheckbox */
	public function setShowNewsletterCheckbox(bool $showNewsletterCheckbox)
		{ $this->_showNewsletterCheckbox = $showNewsletterCheckbox; }
}
