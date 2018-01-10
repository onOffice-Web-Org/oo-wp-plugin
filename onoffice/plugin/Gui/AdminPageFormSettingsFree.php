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

class AdminPageFormSettingsFree
	extends AdminPageFormSettingsBase
{
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

		$fieldNamesEstate = $this->readFieldnamesByContent(onOfficeSDK::MODULE_ESTATE);
		$this->addFieldsConfiguration(onOfficeSDK::MODULE_ESTATE,
			$pFormModelBuilder, $fieldNamesEstate);

		$fieldNamesAddress = $this->readFieldnamesByContent(onOfficeSDK::MODULE_ADDRESS);
		$this->addFieldsConfiguration(onOfficeSDK::MODULE_ADDRESS, $pFormModelBuilder,
			$fieldNamesAddress);

		$this->addSortableFieldsList(array(onOfficeSDK::MODULE_ESTATE, onOfficeSDK::MODULE_ADDRESS),
			$pFormModelBuilder, InputModelBase::HTML_TYPE_COMPLEX_SORTABLE_DETAIL_LIST_FORM);
	}


	/**
	 *
	 */

	protected function generateAccordionBoxes()
	{
		$fieldNamesEstate = array_keys($this->readFieldnamesByContent(onOfficeSDK::MODULE_ESTATE));
		$fieldNamesAddress = array_keys($this->readFieldnamesByContent(onOfficeSDK::MODULE_ADDRESS));
		$fieldNames = $fieldNamesEstate + $fieldNamesAddress;

		foreach ($fieldNames as $category)
		{
			$pFormFieldsConfig = $this->getFormModelByGroupSlug($category);
			$this->createMetaBoxByForm($pFormFieldsConfig, 'side');
		}
	}
}
