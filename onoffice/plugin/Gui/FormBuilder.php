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
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Renderer;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class FormBuilder
{
	/** @var Model\FormModel */
	private $_pFormModel = null;

	/**
	 *
	 * @param \onOffice\WPlugin\Model\FormModel $pFormModel
	 *
	 */

	public function __construct(FormModel $pFormModel)
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
	 * @param \onOffice\WPlugin\Model\InputModelBase $pInputModel
	 * @return Renderer\InputFieldRenderer
	 *
	 */

	private function createInputField(InputModelBase $pInputModel)
	{
		$pInstance = null;

		switch ($pInputModel->getHtmlType())
		{
			case InputModelOption::HTML_TYPE_SELECT:
				$pInstance = new Renderer\InputFieldSelectRenderer($pInputModel->getIdentifier(),
				$pInputModel->getValuesAvailable());
				$pInstance->setSelectedValue($pInputModel->getValue());
				break;

			case InputModelOption::HTML_TYPE_CHECKBOX:
				$name = $pInputModel->getIdentifier();
				if ($pInputModel->getIsMulti()) {
					$name .= '[]';
				}
				$pInstance = new Renderer\InputFieldCheckboxRenderer($name,
				$pInputModel->getValuesAvailable());
				$pInstance->setCheckedValues($pInputModel->getValue());
				break;

			case InputModelOption::HTML_TYPE_RADIO:
				$pInstance = new Renderer\InputFieldRadioRenderer($pInputModel->getIdentifier(),
				$pInputModel->getValuesAvailable());
				$pInstance->setCheckedValue($pInputModel->getValue());
				break;

			case InputModelOption::HTML_TYPE_TEXT:
				$pInstance = new Renderer\InputFieldTextRenderer($pInputModel->getIdentifier());
				$pInstance->addAdditionalAttribute('size', '50');

				if ($pInputModel->getIsPassword())
				{
					$pInstance->addAdditionalAttribute('placeholder',
						__('(remains unchanged)', 'onoffice'));
				}
				else
				{
					$pInstance->setValue($pInputModel->getValue());
				}

				break;
		}

		if ($pInstance !== null) {
			$pInstance->addAdditionalAttribute('class', 'onoffice-input');
		}

		return $pInstance;
	}
}
