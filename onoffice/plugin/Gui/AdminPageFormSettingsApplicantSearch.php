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

namespace onOffice\WPlugin\Gui;

use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModelBase;
use stdClass;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigForm;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class AdminPageFormSettingsApplicantSearch
	extends AdminPageFormSettingsBase
{
	/**
	 *
	 */

	protected function buildForms()
	{
		parent::buildForms();
		$pFormModelBuilder = $this->getFormModelBuilder();

		$pInputModelResultLimit = $pFormModelBuilder->createInputModelResultLimit();
		$pInputModelCaptcha = $pFormModelBuilder->createInputModelCaptchaRequired();

		$pFormModelFormSpecific = new FormModel();
		$pFormModelFormSpecific->setPageSlug($this->getPageSlug());
		$pFormModelFormSpecific->setGroupSlug(self::FORM_VIEW_FORM_SPECIFIC);
		$pFormModelFormSpecific->setLabel(__('Form Specific Options', 'onoffice'));
		$pFormModelFormSpecific->addInputModel($pInputModelResultLimit);
		$pFormModelFormSpecific->addInputModel($pInputModelCaptcha);
		$this->addFormModel($pFormModelFormSpecific);

		$this->addFieldConfigurationForMainModules($pFormModelBuilder);

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
	 * @param stdClass $values
	 *
	 */

	protected function prepareValues(stdClass $values) {

		parent::prepareValues($values);

		$pInputModelFactory = new InputModelDBFactory(new InputModelDBFactoryConfigForm());
		$pInputModelAvOpt = $pInputModelFactory->create
			(InputModelDBFactoryConfigForm::INPUT_FORM_AVAILABLE_OPTIONS, 'availableOptions', true);
		$identifierAvOpt = $pInputModelAvOpt->getIdentifier();

		$pInputModelFieldName = $pInputModelFactory->create
			(InputModelDBFactory::INPUT_FIELD_CONFIG, 'fields', true);
		$identifierFieldName = $pInputModelFieldName->getIdentifier();

		if (property_exists($values, $identifierAvOpt) &&
			property_exists($values, $identifierFieldName)) {
			$fieldsArray = (array)$values->$identifierFieldName;
			$avOptFields = (array)$values->$identifierAvOpt;
			$newAvOptFields = array_fill_keys(array_keys($fieldsArray), '0');

			foreach ($avOptFields as $avOptField) {
				$keyIndex = array_search($avOptField, $fieldsArray);
				$newAvOptFields[$keyIndex] = '1';
			}

			$values->$identifierAvOpt = $newAvOptFields;
		} else {
			$values->$identifierAvOpt = array();
		}
	}
}
