<?php

/**
 *
 *    Copyright (C) 2018-2019 onOffice GmbH
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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationApplicantSearch;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigForm;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelBuilder\InputModelBuilderGeoRange;
use onOffice\WPlugin\Record\BooleanValueToFieldList;
use stdClass;
use function __;

/**
 *
 */

class AdminPageFormSettingsApplicantSearch
	extends AdminPageFormSettingsBase
{
	/** */
	const FORM_VIEW_GEOFIELDS = 'geofields';


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

		$pDataFormConfiguration = new DataFormConfigurationApplicantSearch;
		$pDataFormConfiguration->setId($this->getListViewId() ?? 0);
		$pDataFormConfiguration->setFormType(Form::TYPE_APPLICANT_SEARCH);

		$pFormModelGeoFields = new FormModel();
		$pFormModelGeoFields->setPageSlug($this->getPageSlug());
		$pFormModelGeoFields->setGroupSlug(self::FORM_VIEW_GEOFIELDS);
		$pFormModelGeoFields->setLabel(__('Geo Fields', 'onoffice'));
		$pInputModelBuilderGeoRange = new InputModelBuilderGeoRange(onOfficeSDK::MODULE_SEARCHCRITERIA);
		foreach ($pInputModelBuilderGeoRange->build($pDataFormConfiguration) as $pInputModel) {
			$pFormModelGeoFields->addInputModel($pInputModel);
		}

		$this->addFormModel($pFormModelGeoFields);
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

		$pFormGeoPosition = $this->getFormModelByGroupSlug(self::FORM_VIEW_GEOFIELDS);
		$this->createMetaBoxByForm($pFormGeoPosition, 'normal');

		parent::generateMetaBoxes();
	}


	/**
	 *
	 * @param stdClass $pValues
	 *
	 */

	protected function prepareValues(stdClass $pValues) {

		parent::prepareValues($pValues);
		$pBoolToFieldList = new BooleanValueToFieldList(new InputModelDBFactoryConfigForm, $pValues);
		$pBoolToFieldList->fillCheckboxValues(InputModelDBFactoryConfigForm::INPUT_FORM_AVAILABLE_OPTIONS);
	}
}
