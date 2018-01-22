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
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderForm;
use onOffice\WPlugin\Model\InputModelBase;

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
		add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );
		$pFormModelBuilder = new FormModelBuilderForm($this->getPageSlug());
		$pFormModelBuilder->setFormType($this->getType());
		$pFormModel = $pFormModelBuilder->generate($this->getListViewId());
		$this->addFormModel($pFormModel);

		$pInputModelName = $pFormModelBuilder->createInputModelName();
		$pFormModelName = new FormModel();
		$pFormModelName->setPageSlug($this->getPageSlug());
		$pFormModelName->setGroupSlug(self::FORM_RECORD_NAME);
		$pFormModelName->setLabel(__('choose name', 'onoffice'));
		$pFormModelName->addInputModel($pInputModelName);
		$this->addFormModel($pFormModelName);

		$pInputModelTemplate = $pFormModelBuilder->createInputModelTemplate();
		$pFormModelLayoutDesign = new FormModel();
		$pFormModelLayoutDesign->setPageSlug($this->getPageSlug());
		$pFormModelLayoutDesign->setGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$pFormModelLayoutDesign->setLabel(__('Layout & Design', 'onoffice'));
		$pFormModelLayoutDesign->addInputModel($pInputModelTemplate);
		$this->addFormModel($pFormModelLayoutDesign);

		$pInputModelResultLimit = $pFormModelBuilder->createInputModelResultLimit();
		$pFormModelFormSpecific = new FormModel();
		$pFormModelFormSpecific->setPageSlug($this->getPageSlug());
		$pFormModelFormSpecific->setGroupSlug(self::FORM_VIEW_FORM_SPECIFIC);
		$pFormModelFormSpecific->setLabel(__('Form Specific', 'onoffice'));
		$pFormModelFormSpecific->addInputModel($pInputModelResultLimit);
		$this->addFormModel($pFormModelFormSpecific);

		$this->addFieldConfigurationForMainModules($pFormModelBuilder);

		$this->addSortableFieldsList($this->getSortableFieldModules(), $pFormModelBuilder,
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
}
