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

namespace onOffice\WPlugin\Renderer;

use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Utility\__String;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class InputModelRenderer
{
	/** @var FormModel */
	private $_pFormModel = null;

	/**
	 *
	 * @param FormModel $pFormModel
	 *
	 */

	public function __construct(FormModel $pFormModel = null)
	{
		$this->_pFormModel = $pFormModel;
	}


	/**
	 *
	 */

	public function buildForm()
	{
		$pForm = $this->_pFormModel;

		add_settings_section($pForm->getGroupSlug(), $pForm->getLabel(),
			function(){}, $pForm->getPageSlug());
		settings_fields($pForm->getGroupSlug());

		foreach ($pForm->getInputModel() as $pInputModel)
		{
			$pInputField = $this->createInputField($pInputModel);

			add_settings_field( $pInputModel->getIdentifier(), $pInputModel->getLabel(),
				array($pInputField, 'render'), $pForm->getPageSlug(), $pForm->getGroupSlug() );
		}
	}


	/**
	 *
	 */

	public function buildForAjax() {
		$pForm = $this->_pFormModel;

		if ($pForm->getIsInvisibleForm()) {
			return;
		}

		foreach ($pForm->getInputModel() as $pInputModel)
		{
			$pInputField = $this->createInputField($pInputModel);
			echo '<p id="" class="wp-clearfix">';
			echo '<label class="howto" for="'.esc_html($pInputField->getGuiId()).'">';
			echo esc_html__($pInputModel->getLabel());
			echo '</label>';
			$pInputField->render();
			echo '</p>';
		}
	}


	/**
	 *
	 */

	public function registerFields()
	{
		$pForm = $this->_pFormModel;

		foreach ($pForm->getInputModel() as $pInputModel)
		{
			if ($pInputModel instanceof InputModelOption)
			{
				register_setting( $pForm->getGroupSlug(), $pInputModel->getIdentifier(),
					array
					(
						'type' => $pInputModel->getType(),
						'description' => $pInputModel->getDescription(),
						'sanitize_callback' => $pInputModel->getSanitizeCallback(),
						'show_in_rest' => $pInputModel->getShowInRest(),
						'default' => $pInputModel->getDefault(),
					));
			}
		}
	}


	/**
	 *
	 * @param InputModelBase $pInputModel
	 * @return InputFieldRenderer
	 *
	 */

	private function createInputField(InputModelBase $pInputModel)
	{
		$pInstance = null;
		$onOfficeInputFields = true;

		switch ($pInputModel->getHtmlType())
		{
			case InputModelOption::HTML_TYPE_SELECT:
				$pInstance = new InputFieldSelectRenderer($pInputModel->getIdentifier(),
				$pInputModel->getValuesAvailable());
				$pInstance->setSelectedValue($pInputModel->getValue());
				break;

			case InputModelOption::HTML_TYPE_CHECKBOX:
				$name = $pInputModel->getIdentifier();
				if ($pInputModel->getIsMulti()) {
					$name .= '[]';
				}
				$pInstance = new InputFieldCheckboxRenderer($name,
				$pInputModel->getValuesAvailable());
				$pInstance->setCheckedValues($pInputModel->getValue());
				break;

			case InputModelOption::HTML_TYPE_COMPLEX_SORTABLE_CHECKBOX_LIST:
				$name = $pInputModel->getIdentifier();
				$pInstance = new InputFieldComplexSortableListRenderer($name,
				$pInputModel->getValuesAvailable());
				$pInstance->setCheckedValues($pInputModel->getValue());
				break;

			case InputModelOption::HTML_TYPE_COMPLEX_SORTABLE_DETAIL_LIST:
				$name = $pInputModel->getIdentifier();
				$pContent = new InputFieldComplexSortableDetailListContentDefault();
				$pInstance = new InputFieldComplexSortableDetailListRenderer($name,
						array($pInputModel->getValue()));
				$pInstance->setContentRenderer($pContent);
				$pInstance->setAllFields($pInputModel->getValuesAvailable());
				break;
			case InputModelOption::HTML_TYPE_COMPLEX_SORTABLE_DETAIL_LIST_FORM:
				$name = $pInputModel->getIdentifier();
				$pContent = new InputFieldComplexSortableDetailListContentForm();
				$pContent->setExtraInputModels($pInputModel->getReferencedInputModels());
				$pInstance = new InputFieldComplexSortableDetailListRenderer($name,
						array($pInputModel->getValue()));
				$pInstance->setContentRenderer($pContent);
				$pInstance->setAllFields($pInputModel->getValuesAvailable());
				break;

			case InputModelOption::HTML_TYPE_CHECKBOX_BUTTON:
				$name = $pInputModel->getIdentifier();
				$onOfficeInputFields = false;
				if ($pInputModel->getIsMulti()) {
					$name .= '[]';
				}
				$pInstance = new InputFieldCheckboxButtonRenderer($name,
				$pInputModel->getValuesAvailable());
				$pInstance->setCheckedValues($pInputModel->getValue());
				$pInstance->setId($pInputModel->getId());
				$pInstance->addAdditionalAttribute('class', 'onoffice-possible-input');

				if ($pInputModel->getSpecialDivId() != null)
				{
					$pInstance->addAdditionalAttribute('data-action-div', $pInputModel->getSpecialDivId());
				}
				break;

			case InputModelOption::HTML_TYPE_RADIO:
				$pInstance = new InputFieldRadioRenderer($pInputModel->getIdentifier(),
				$pInputModel->getValuesAvailable());
				$pInstance->setCheckedValue($pInputModel->getValue());
				break;

			case InputModelOption::HTML_TYPE_TEXT:
				$pInstance = new InputFieldTextRenderer('text', $pInputModel->getIdentifier());
				$pInstance->addAdditionalAttribute('size', '50');

				if ($pInputModel->getIsPassword())
				{
					$pInstance->addAdditionalAttribute('placeholder',
						__('(Remains unchanged)', 'onoffice'));
				}
				else
				{
					$placeholder = $pInputModel->getPlaceholder();

					if (!__String::getNew($placeholder)->isEmpty())
					{
						$pInstance->addAdditionalAttribute('placeholder', $placeholder);
					}
					$pInstance->setValue($pInputModel->getValue());
				}

				break;
			case InputModelOption::HTML_TYPE_HIDDEN:
				$name = $pInputModel->getIdentifier();
				if ($pInputModel->getIsMulti()) {
					$name .= '[]';
				}
				$pInstance = new InputFieldTextRenderer('hidden', $name);
				$pInstance->setValue($pInputModel->getValue());

				break;
		}

		if ($pInstance !== null) {
			if ($onOfficeInputFields) {
				$pInstance->addAdditionalAttribute('class', 'onoffice-input');
			}

			if ($pInputModel instanceof InputModelDB) {
				if (!__String::getNew($pInputModel->getModule())->isEmpty()) {
					$module = $pInputModel->getModule();
					$pInstance->addAdditionalAttribute('data-onoffice-module', $module);
				}

				if ($pInputModel->getIgnore()) {
					$pInstance->addAdditionalAttribute('data-onoffice-ignore', 'true');
				}

			}
		}


		return $pInstance;
	}

	/** @return FormModel */
	public function getFormModel()
		{ return $this->_pFormModel; }

	/** @param \onOffice\WPlugin\Form\Model\FormModel $pFormModel */
	public function setFormModel(FormModel $pFormModel)
		{ $this->_pFormModel = $pFormModel; }
}
