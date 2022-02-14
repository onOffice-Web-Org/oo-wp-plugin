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

namespace onOffice\WPlugin\Model\FormModelBuilder;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\SortList\SortListTypes;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorGeoPositionBackend;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorInternalAnnotations;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigEstate;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Record\RecordManagerReadListViewEstate;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\ImageTypes;
use function __;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class FormModelBuilderDBEstateListSettings
	extends FormModelBuilderDB
{
	/** */
	const DEFAULT_RECORDS_PER_PAGE = 20;


	/** @var string[] */
	private static $_defaultFields = array(
		'objekttitel',
		'objektart',
		'objekttyp',
		'vermarktungsart',
		'plz',
		'ort',
		'bundesland',
		'objektnr_extern',
		'wohnflaeche',
		'grundstuecksflaeche',
		'nutzflaeche',
		'anzahl_zimmer',
		'anzahl_badezimmer',
		'kaufpreis',
		'kaltmiete',
	);


	/**
	 *
	 */

	public function __construct()
	{
		$pConfig = new InputModelDBFactoryConfigEstate();
		$this->setInputModelDBFactory(new InputModelDBFactory($pConfig));

		$pFieldCollection = new FieldModuleCollectionDecoratorInternalAnnotations
			(new FieldModuleCollectionDecoratorGeoPositionBackend(new FieldsCollection()));
		$pFieldnames = new Fieldnames($pFieldCollection);
		$this->setFieldnames($pFieldnames);
	}


	/**
	 *
	 * @param string $pageSlug
	 * @param int $listViewId
	 * @return FormModel
	 *
	 */

	public function generate(string $pageSlug, $listViewId = null): FormModel
	{
		if ($listViewId !== null)
		{
			$pRecordReadManager = new RecordManagerReadListViewEstate();
			$values = $pRecordReadManager->getRowById($listViewId);
			if ((int)$values['recordsPerPage'] === 0) {
				$values['recordsPerPage'] = self::DEFAULT_RECORDS_PER_PAGE;
			}
			$this->setValues($values);
		}
		else
		{
			$this->setValues(array(
				DataListView::FIELDS => self::$_defaultFields,
				'recordsPerPage' => self::DEFAULT_RECORDS_PER_PAGE,
			));
		}

		$pFormModel = new FormModel();
		$pFormModel->setLabel(__('List View', 'onoffice-for-wp-websites'));
		$pFormModel->setGroupSlug('onoffice-listview-settings-main');
		$pFormModel->setPageSlug($pageSlug);

		return $pFormModel;
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

		$availableFilters = array(0 => '') + $this->readFilters(onOfficeSDK::MODULE_ESTATE);

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

	public function getInputModelIsFilterable()
	{
		$pInputModelFactoryConfig = new InputModelDBFactoryConfigEstate();
		$pInputModelFactory = new InputModelDBFactory($pInputModelFactoryConfig);
		$label = __('Filterable', 'onoffice-for-wp-websites');
		$type = InputModelDBFactoryConfigEstate::INPUT_FIELD_FILTERABLE;
		/* @var $pInputModel InputModelDB */
		$pInputModel = $pInputModelFactory->create($type, $label, true);
		$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModel->setValueCallback(array($this, 'callbackValueInputModelIsFilterable'));

		return $pInputModel;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function getInputModelAvailableOptions()
	{
		$pInputModelFactoryConfig = new InputModelDBFactoryConfigEstate();
		$pInputModelFactory = new InputModelDBFactory($pInputModelFactoryConfig);
		$label = __('Reduce values according to selected filter', 'onoffice-for-wp-websites');
		$type = InputModelDBFactoryConfigEstate::INPUT_FIELD_AVAILABLE_OPTIONS;
		/* @var $pInputModel InputModelDB */
		$pInputModel = $pInputModelFactory->create($type, $label, true);
		$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModel->setValueCallback(array($this, 'callbackValueInputModelAvailableOptions'));

		return $pInputModel;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function getInputModelIsHidden()
	{
		$pInputModelFactoryConfig = new InputModelDBFactoryConfigEstate();
		$pInputModelFactory = new InputModelDBFactory($pInputModelFactoryConfig);
		$label = __('Hidden', 'onoffice-for-wp-websites');
		$type = InputModelDBFactoryConfigEstate::INPUT_FIELD_HIDDEN;
		/* @var $pInputModel InputModelDB */
		$pInputModel = $pInputModelFactory->create($type, $label, true);
		$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModel->setValueCallback(array($this, 'callbackValueInputModelIsHidden'));

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


	/**
	 *
	 * @param InputModelBase $pInputModel
	 * @param string $key Name of input
	 *
	 */

	public function callbackValueInputModelAvailableOptions(InputModelBase $pInputModel, string $key)
	{
		$valueFromConf = $this->getValue('availableOptions');

		$availableOptionsFields = is_array($valueFromConf) ? $valueFromConf : array();
		$value = in_array($key, $availableOptionsFields);
		$pInputModel->setValue($value);
		$pInputModel->setValuesAvailable($key);
	}


	/**
	 *
	 * @param string $module
	 * @param string $htmlType
	 * @param bool $isShow
	 * @return InputModelDB
	 *
	 */

	public function createSortableFieldList($module, $htmlType, bool $isShow = true): InputModelDB
	{
		$pSortableFieldsList = parent::createSortableFieldList($module, $htmlType);
		if (! $isShow) {
			return $pSortableFieldsList;
		}
		$pInputModelIsFilterable = $this->getInputModelIsFilterable();
		$pInputModelIsHidden = $this->getInputModelIsHidden();
		$pInputModelIsAvailableOptions = $this->getInputModelAvailableOptions();
		$pSortableFieldsList->addReferencedInputModel($pInputModelIsFilterable);
		$pSortableFieldsList->addReferencedInputModel($pInputModelIsHidden);
		$pSortableFieldsList->addReferencedInputModel($pInputModelIsAvailableOptions);

		return $pSortableFieldsList;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelListType()
	{
		$labelListType = __('Type of List', 'onoffice-for-wp-websites');
		$pInputModelListType = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_LIST_TYPE, $labelListType);
		$pInputModelListType->setHtmlType(InputModelOption::HTML_TYPE_SELECT);
		$pInputModelListType->setValue($this->getValue($pInputModelListType->getField()));
		$pInputModelListType->setValuesAvailable(self::getListViewLabels());

		return $pInputModelListType;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelShowStatus()
	{
		$labelShowStatus = __('Show Estate Status', 'onoffice-for-wp-websites');

		$pInputModelShowStatus = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_SHOW_STATUS, $labelShowStatus);
		$pInputModelShowStatus->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelShowStatus->setValue($this->getValue('show_status'));
		$pInputModelShowStatus->setValuesAvailable(1);

		return $pInputModelShowStatus;
	}

	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelShowReferenceEstate()
	{
		$labelShowReferenceEstate = __('Show reference estates', 'onoffice-for-wp-websites');

		$pInputModelShowStatus = $this->getInputModelDBFactory()->create
		(InputModelDBFactory::INPUT_SHOW_REFERENCE_ESTATE, $labelShowReferenceEstate);
		$pInputModelShowStatus->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelShowStatus->setValue($this->getValue('show_reference_estate') ?? 1);
		$pInputModelShowStatus->setValuesAvailable(1);

		return $pInputModelShowStatus;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelRandomSort()
	{
		$labelRandom = __('Random Order', 'onoffice-for-wp-websites');

		$pInputModelShowStatus = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_RANDOM_ORDER, $labelRandom);
		$pInputModelShowStatus->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelShowStatus->setValue($this->getValue('random'));
		$pInputModelShowStatus->setValuesAvailable(1);

		return $pInputModelShowStatus;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelPictureTypes()
	{
		$allPictureTypes = ImageTypes::getAllImageTypesTranslated();

		$pInputModelPictureTypes = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_PICTURE_TYPE, null, true);
		$pInputModelPictureTypes->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelPictureTypes->setValuesAvailable($allPictureTypes);
		$pictureTypes = $this->getValue(DataListView::PICTURES);

		if (null == $pictureTypes)
		{
			$pictureTypes = array();
		}

		$pInputModelPictureTypes->setValue($pictureTypes);

		return $pInputModelPictureTypes;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelExpose()
	{
		$labelExpose = __('PDF-Expose', 'onoffice-for-wp-websites');

		$pInputModelExpose = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_EXPOSE, $labelExpose);
		$pInputModelExpose->setHtmlType(InputModelOption::HTML_TYPE_SELECT);
		$exposes = array('' => '') + $this->readExposes();
		$pInputModelExpose->setValuesAvailable($exposes);
		$pInputModelExpose->setValue($this->getValue($pInputModelExpose->getField()));

		return $pInputModelExpose;
	}


	/**
	 *
	 * @return array enum values from DB
	 *
	 */

	static public function getListViewLabels()
	{
		return array(
			DataListView::LISTVIEW_TYPE_DEFAULT => __('Default', 'onoffice-for-wp-websites'),
			DataListView::LISTVIEW_TYPE_REFERENCE => __('Reference Estates', 'onoffice-for-wp-websites'),
			DataListView::LISTVIEW_TYPE_FAVORITES => __('Favorites List', 'onoffice-for-wp-websites'),
		);
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelSortBySetting()
	{
		$label = __('Sort by User Selection', 'onoffice-for-wp-websites');
		$pInputModel = $this->getInputModelDBFactory()->create
				(InputModelDBFactory::INPUT_SORT_BY_SETTING, $label);
		$pInputModel->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModel->setValue($this->getValue($pInputModel->getField()));
		$pInputModel->setValuesAvailable(1);

		return $pInputModel;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelSortByChosen()
	{
		$label = __('Sort by', 'onoffice-for-wp-websites');
		$pInputModel = $this->getInputModelDBFactory()->create
				(InputModelDBFactory::INPUT_SORT_BY_CHOSEN, $label, true);
		$pInputModel->setHtmlType(InputModelOption::HTML_TYPE_CHOSEN);
		$fieldnames = $this->getOnlyDefaultSortByFields(onOfficeSDK::MODULE_ESTATE);
		$pInputModel->setValuesAvailable($fieldnames);
		$value = $this->getValue(DataListView::SORT_BY_USER_VALUES);

		if ($value == null) {
			$value = [];
		}
		$pInputModel->setValue($value);
		return $pInputModel;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelSortByDefault()
	{
		$label = __('Standard Sort', 'onoffice-for-wp-websites');
		$pInputModel = $this->getInputModelDBFactory()->create(InputModelDBFactory::INPUT_SORT_BY_DEFAULT, $label);
		$pInputModel->setHtmlType(InputModelOption::HTML_TYPE_SELECT);

		$selectedValue = $this->getValue($pInputModel->getField());
		$pInputModel->setValue($selectedValue);
		$values = $this->getValue(DataListView::SORT_BY_USER_VALUES);

		$sortBySpec = $this->getValue(InputModelDBFactory::INPUT_SORT_BY_USER_DEFINED_DIRECTION);

		if ($values == null) {
			$values = [];
		}
		$fieldnames = $this->getOnlyDefaultSortByFields(onOfficeSDK::MODULE_ESTATE);
		$defaultValues = [];

		foreach ($values as $value)	{
			if (array_key_exists($value, $fieldnames)) {
				$defaultValues[$value.'#'.SortListTypes::SORTORDER_ASC] =
					$fieldnames[$value].' ('.SortListTypes::getSortOrderMapping(
						$sortBySpec, SortListTypes::SORTORDER_ASC).')';

				$defaultValues[$value.'#'.SortListTypes::SORTORDER_DESC] =
					$fieldnames[$value].' ('.SortListTypes::getSortOrderMapping(
						$sortBySpec, SortListTypes::SORTORDER_DESC).')';
			}
		}

		$pInputModel->setValuesAvailable($defaultValues);
		return $pInputModel;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelSortBySpec()
	{
		$userDefinedSortDirectionValues = [
			'0' => __('lowest First / highest First', 'onoffice-for-wp-websites'),
			'1' => __('ascending / descending', 'onoffice-for-wp-websites'),
		];

		$label = __('Formulation of sorting directions', 'onoffice-for-wp-websites');
		$pInputModel = $this->getInputModelDBFactory()->create(InputModelDBFactory::INPUT_SORT_BY_USER_DEFINED_DIRECTION, $label);
		$pInputModel->setHtmlType(InputModelOption::HTML_TYPE_SELECT);
		$pInputModel->setValue($this->getValue($pInputModel->getField()));
		$pInputModel->setValuesAvailable($userDefinedSortDirectionValues);
		return $pInputModel;
	}
}
