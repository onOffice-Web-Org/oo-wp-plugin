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

namespace onOffice\WPlugin\Model\FormModelBuilder;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorReadAddress;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigAddress;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigEstate;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelLabel;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Record\RecordManagerReadListViewAddress;
use onOffice\WPlugin\Types\FieldsCollection;
use function __;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class FormModelBuilderDBAddress
	extends FormModelBuilderDB
{
	/** */
	const DEFAULT_RECORDS_PER_PAGE = 20;


	/**
	 *
	 */

	public function __construct()
	{
		$pInputModelDBFactoryConfig = new InputModelDBFactoryConfigAddress();
		$pInputModelDBFactory = new InputModelDBFactory($pInputModelDBFactoryConfig);
		$this->setInputModelDBFactory($pInputModelDBFactory);

		$pFieldsCollection = new FieldModuleCollectionDecoratorReadAddress(new FieldsCollection());
		$pFieldnames = new Fieldnames($pFieldsCollection);
		$pFieldnames->loadLanguage();
		$this->setFieldnames($pFieldnames);
	}


	/**
	 *
	 */

	public function generate(string $pageSlug, $listViewId = null): FormModel
	{
		$this->setValues([
			'recordsPerPage' => self::DEFAULT_RECORDS_PER_PAGE,
		]);
		if ($listViewId !== null)
		{
			$pRecordReadManager = new RecordManagerReadListViewAddress();
			$values = $pRecordReadManager->getRowById($listViewId);
			$resultByField = $pRecordReadManager->readFieldconfigByListviewId($listViewId);
			$values['fields'] = array_column($resultByField, 'fieldname');
			$values['filterable'] = $this->arrayColumnTrue($resultByField, 'filterable');
			$values['hidden'] = $this->arrayColumnTrue($resultByField, 'hidden');

			if ((int)$values['recordsPerPage'] === 0) {
				$values['recordsPerPage'] = self::DEFAULT_RECORDS_PER_PAGE;
			}
			$this->setValues($values);
		}

		$pFormModel = new FormModel();
		$pFormModel->setLabel(__('List View', 'onoffice-for-wp-websites'));
		$pFormModel->setGroupSlug('onoffice-listview-address-settings-main');
		$pFormModel->setPageSlug($pageSlug);

		return $pFormModel;
	}


	/**
	 *
	 * @param array $array
	 * @param string $column
	 * @return array
	 *
	 */

	private function arrayColumnTrue(array $array, string $column): array
	{
		$columns = array_column($array, $column, 'fieldname');
		return array_keys(array_filter($columns));
	}

    /**
     * @return InputModelLabel
     */
    public function createInputModelEmbedCode()
    {
        $pConfig = new InputModelDBFactoryConfigEstate();
        $config = $pConfig->getConfig();
        $name = $config[InputModelDBFactory::INPUT_LISTNAME]
        [InputModelDBFactoryConfigEstate::KEY_FIELD];

        $listName = $this->getValue($name);

        $codes = '[oo_address view="'.$listName.'"]';
        $pInputModeLabel = new InputModelLabel(__('Shortcode: ', 'onoffice-for-wp-websites'), $codes);
        $pInputModeLabel->setHtmlType(InputModelBase::HTML_TYPE_LABEL);
        $pInputModeLabel->setValueEnclosure(InputModelLabel::VALUE_ENCLOSURE_CODE);

        return $pInputModeLabel;
    }

    public function createInputModelButton()
    {
        $pConfig = new InputModelDBFactoryConfigEstate();
        $config = $pConfig->getConfig();
        $name = $config[InputModelDBFactory::INPUT_LISTNAME]
        [InputModelDBFactoryConfigEstate::KEY_FIELD];
        $listName = $this->getValue($name);

        $codes = '[oo_address view="'.$listName.'"]';
        $pInputModeLabel = new InputModelLabel('', $codes);
        $pInputModeLabel->setHtmlType(InputModelBase::HTML_TYPE_BUTTON);
        return $pInputModeLabel;
    }

	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelFilter()
	{
		$labelFiltername = __('Filter', 'onoffice-for-wp-websites');
		$pInputModelFiltername = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_FILTERID, $labelFiltername);
		$pInputModelFiltername->setHtmlType(InputModelOption::HTML_TYPE_SELECT);

		$availableFilters = array(0 => '') + $this->readFilters(onOfficeSDK::MODULE_ADDRESS);

		$pInputModelFiltername->setValuesAvailable($availableFilters);
		$filteridSelected = $this->getValue($pInputModelFiltername->getField());
		$pInputModelFiltername->setValue($filteridSelected);

		return $pInputModelFiltername;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelPictureTypes()
	{
		$labelPhoto = __('Passport Photo', 'onoffice-for-wp-websites');
		$pInputModelPictureTypes = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_PICTURE_TYPE, $labelPhoto);
		$pInputModelPictureTypes->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelPictureTypes->setValuesAvailable(1);
		$pictureTypeSelected = $this->getValue($pInputModelPictureTypes->getField());
		$pInputModelPictureTypes->setValue((int)$pictureTypeSelected);

		return $pInputModelPictureTypes;
	}


	/**
	 *
	 * @param string $module
	 * @param string $htmlType
	 * @return InputModelDB
	 *
	 */

	public function createSortableFieldList($module, $htmlType)
	{
		$pSortableFieldsList = parent::createSortableFieldList($module, $htmlType);
		$pInputModelIsFilterable = $this->getInputModelIsFilterable();
		$pInputModelIsHidden = $this->getInputModelIsHidden();
		$pSortableFieldsList->addReferencedInputModel($pInputModelIsFilterable);
		$pSortableFieldsList->addReferencedInputModel($pInputModelIsHidden);

		return $pSortableFieldsList;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function getInputModelIsFilterable()
	{
		$pInputModelFactoryConfig = new InputModelDBFactoryConfigAddress();
		$pInputModelFactory = new InputModelDBFactory($pInputModelFactoryConfig);
		$label = __('Filterable', 'onoffice-for-wp-websites');
		$type = InputModelDBFactoryConfigEstate::INPUT_FIELD_FILTERABLE;
		/* @var $pInputModel InputModelDB */
		$pInputModel = $pInputModelFactory->create($type, $label, true);
		$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModel->setValueCallback([$this, 'callbackValueInputModelIsFilterable']);

		return $pInputModel;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function getInputModelIsHidden()
	{
		$pInputModelFactoryConfig = new InputModelDBFactoryConfigAddress();
		$pInputModelFactory = new InputModelDBFactory($pInputModelFactoryConfig);
		$label = __('Hidden', 'onoffice-for-wp-websites');
		$type = InputModelDBFactoryConfigEstate::INPUT_FIELD_HIDDEN;
		/* @var $pInputModel InputModelDB */
		$pInputModel = $pInputModelFactory->create($type, $label, true);
		$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModel->setValueCallback([$this, 'callbackValueInputModelIsHidden']);

		return $pInputModel;
	}


	/**
	 *
	 * @param InputModelBase $pInputModel
	 * @param string $key Name of input
	 *
	 */

	public function callbackValueInputModelIsFilterable(InputModelBase $pInputModel, $key)
	{
		$valueFromConf = $this->getValue('filterable');
		$filterableFields = is_array($valueFromConf) ? $valueFromConf : array();
		$value = in_array($key, $filterableFields);
		$pInputModel->setValue($value);
		$pInputModel->setValuesAvailable($key);
	}


	/**
	 *
	 * @param InputModelBase $pInputModel
	 * @param string $key Name of input
	 *
	 */

	public function callbackValueInputModelIsHidden(InputModelBase $pInputModel, $key)
	{
		$valueFromConf = $this->getValue('hidden');
		$filterableFields = is_array($valueFromConf) ? $valueFromConf : array();
		$value = in_array($key, $filterableFields);
		$pInputModel->setValue($value);
		$pInputModel->setValuesAvailable($key);
	}
}
