<?php

/**
 *
 *    Copyright (C) 2020 onOffice GmbH
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

namespace onOffice\WPlugin\Model\FormModelBuilder;

use function __;
use DI\NotFoundException;
use DI\DependencyException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Types\ImageTypes;
use onOffice\WPlugin\Utility\__String;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\DataView\DataAddressDetailView;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\DataView\DataAddressDetailViewHandler;
use onOffice\WPlugin\Model\ExceptionInputModelMissingField;
use onOffice\WPlugin\Controller\Exception\UnknownModuleException;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorReadAddress;
use onOffice\WPlugin\Model\InputModel\InputModelOptionFactoryAddressDetailView;
use onOffice\WPlugin\DataFormConfiguration\UnknownFormException;
use onOffice\WPlugin\Record\RecordManagerReadListViewEstate;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2020, onOffice(R) GmbH
 *
 * This class must not use InputModelOption!
 *
 */

class FormModelBuilderAddressDetailSettings
	extends FormModelBuilder
{
	/** @var InputModelOptionFactoryAddressDetailView */
	private $_pInputModelAddressDetailFactory = null;

	/** @var DataAddressDetailView */
	private $_pDataAddressDetail = null;

	/** @var Fieldnames */
	private $_pFieldnames = null;

	/**
	 * @param Fieldnames $pFieldnames
	 */

	public function __construct(Fieldnames $pFieldnames = null)
	{
		$this->_pFieldnames = $pFieldnames ?? new Fieldnames(new FieldsCollection());

		$pFieldsCollection = new FieldsCollection();
		$pFieldnames = $pFieldnames ?? new Fieldnames($pFieldsCollection);
		$pFieldnames->loadLanguage();
		$this->setFieldnames($pFieldnames);
	}

	/**
	 * @param string $pageSlug
	 * @return FormModel
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */

	public function generate(string $pageSlug): FormModel
	{
		$this->_pInputModelAddressDetailFactory = new InputModelOptionFactoryAddressDetailView($pageSlug);
		$pDataAddressDetailViewHandler = new DataAddressDetailViewHandler;
		$this->_pDataAddressDetail = $pDataAddressDetailViewHandler->getAddressDetailView();

		$pFormModel = new FormModel();
		$pFormModel->setLabel(__('Address Detail View', 'onoffice-for-wp-websites'));
		$pFormModel->setGroupSlug('onoffice-address-detail-settings-main');
		$pFormModel->setPageSlug($pageSlug);

		return $pFormModel;
	}

	/**
	 *
	 * @return InputModelOption
	 *
	 * @throws UnknownFormException
	 * @throws ExceptionInputModelMissingField
	 */

	public function createInputModelShortCodeForm()
	{

		$labelShortCodeForm = __('Select Contact Form', 'onoffice-for-wp-websites');
		$pInputModelShortCodeForm = $this->_pInputModelAddressDetailFactory->create
		(InputModelOptionFactoryAddressDetailView::INPUT_SHORT_CODE_FORM, $labelShortCodeForm);
		$pInputModelShortCodeForm->setHtmlType(InputModelBase::HTML_TYPE_SELECT);
		$nameShortCodeForms = array('' => __('No Contact Form', 'onoffice-for-wp-websites')) + $this->readNameShortCodeForm();
		$pInputModelShortCodeForm->setValuesAvailable($nameShortCodeForms);

		$pInputModelShortCodeForm->setValue($this->_pDataAddressDetail->getShortCodeForm());

		return $pInputModelShortCodeForm;
	}

	/**
	 *
	 * @return array
	 *
	 * @throws UnknownFormException
	 */

	protected function readNameShortCodeForm(): array
	{
		$recordManagerReadForm = new RecordManagerReadForm();
		$allRecordsForm = $recordManagerReadForm->getAllRecords();
		$shortCodeForm = array();

		foreach ($allRecordsForm as $value) {
			$form_name = __String::getNew($value->name);
			$shortCodeForm[$value->name] = '[oo_form form=&quot;'
				. esc_html($form_name) . '&quot;]';
		}

		return $shortCodeForm;
	}

	public function createButtonModelFieldsConfigByCategory($category, $fieldNames, $categoryLabel) {

	}

	public function createInputModelFieldsConfigByCategory($category, $fieldNames, $categoryLabel) {

	}
}
