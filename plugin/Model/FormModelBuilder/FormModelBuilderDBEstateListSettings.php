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
use onOffice\WPlugin\Model\InputModelLabel;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use DI\ContainerBuilder;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\WP\InstalledLanguageReader;
use onOffice\WPlugin\Model\InputModelBuilder\InputModelBuilderCustomLabel;
use function __;
use DI\DependencyException;
use DI\NotFoundException;
use DI\Container;

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
	const DEFAULT_RECORDS_PER_PAGE = 12;

	/** */
	const DEFAULT_RECORDS_SHOW_STATUS = 1;

	/** @var string[] */
	private static $_defaultFields = array(
		'objekttitel',
		'objektart',
		'objekttyp',
		'vermarktungsart',
		'plz',
		'ort',
		'objektnr_extern',
		'wohnflaeche',
		'grundstuecksflaeche',
		'nutzflaeche',
		'anzahl_zimmer',
		'anzahl_badezimmer',
		'kaufpreis',
		'kaltmiete',
	);

	/** @var Container */
	private $_pContainer = null;

	/**
	 * @param Container $pContainer
	 */

	public function __construct(Container $pContainer = null)
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainer ?? $pContainerBuilder->build();
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
				'show_status' => self::DEFAULT_RECORDS_SHOW_STATUS,
			));
		}

		$pFormModel = new FormModel();
		$pFormModel->setLabel(__('List View', 'onoffice-for-wp-websites'));
		$pFormModel->setGroupSlug('onoffice-listview-settings-main');
		$pFormModel->setPageSlug($pageSlug);

		return $pFormModel;
	}


	/**
	 * @return InputModelLabel
	 */

	public function createInputModelEmbedCode()
	{
		$pConfig = new InputModelDBFactoryConfigEstate();
		$config  = $pConfig->getConfig();
		$name    = $config[ InputModelDBFactory::INPUT_LISTNAME ]
		[ InputModelDBFactoryConfigEstate::KEY_FIELD ];

		$listName = $this->getValue( $name );

		$codes           = '[oo_estate view="' . $listName . '"]';
		$pInputModeLabel = new InputModelLabel( __( 'Shortcode: ', 'onoffice-for-wp-websites' ), $codes );
		$pInputModeLabel->setHtmlType( InputModelBase::HTML_TYPE_LABEL );
		$pInputModeLabel->setValueEnclosure( InputModelLabel::VALUE_ENCLOSURE_CODE );

		return $pInputModeLabel;
	}


	/**
	 * @return InputModelLabel
	 */

	public function createInputModelButton()
	{
		$pConfig  = new InputModelDBFactoryConfigEstate();
		$config   = $pConfig->getConfig();
		$name     = $config[ InputModelDBFactory::INPUT_LISTNAME ]
		[ InputModelDBFactoryConfigEstate::KEY_FIELD ];
		$listName = $this->getValue( $name );

		$codes           = '[oo_estate view="' . $listName . '"]';
		$pInputModeLabel = new InputModelLabel( '', $codes );
		$pInputModeLabel->setHtmlType( InputModelBase::HTML_TYPE_BUTTON );

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

		$availableFilters = array(0 => '') + $this->readFilters(onOfficeSDK::MODULE_ESTATE);

		$pInputModelFiltername->setValuesAvailable($availableFilters);
		$filteridSelected = $this->getValue($pInputModelFiltername->getField());
		$pInputModelFiltername->setValue($filteridSelected);
		$linkUrl = __("https://de.enterprisehilfe.onoffice.com/help_entries/property-filter/?lang=en","onoffice-for-wp-websites");
		$linkLabel = '<a href="' . $linkUrl . '" target="_blank">' . __( 'Learn more.', 'onoffice-for-wp-websites' ) . '</a>';
		$pInputModelFiltername->setHintHtml( sprintf( __( 'Choose an estate filter from onOffice enterprise. %s',
			'onoffice-for-wp-websites' ), $linkLabel ) );

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
		$label = __('Show in search', 'onoffice-for-wp-websites');
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
		$label = __('Hide empty values from onOffice enterprise', 'onoffice-for-wp-websites');
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
	 * @return InputModelDB
	 *
	 */

	public function getInputModelConvertInputTextToSelectCityField()
	{
		$pInputModelFactoryConfig = new InputModelDBFactoryConfigEstate();
		$pInputModelFactory = new InputModelDBFactory($pInputModelFactoryConfig);
		$label = __('Display as selection list instead of text input', 'onoffice-for-wp-websites');
		$type = InputModelDBFactoryConfigEstate::INPUT_FIELD_CONVERT_TEXT_TO_SELECT_FOR_CITY_FIELD;
		/* @var $pInputModel InputModelDB */
		$pInputModel = $pInputModelFactory->create($type, $label, true);
		$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModel->setValueCallback(array($this, 'callbackValueInputModelConvertInputTextToSelectCityField'));

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
	 * @param InputModelBase $pInputModel
	 * @param string $key Name of input
	 *
	 */

	public function callbackValueInputModelConvertInputTextToSelectCityField(InputModelBase $pInputModel, string $key)
	{
		$valueFromConfig = $this->getValue('convertTextToSelectForCityField');

		$convertTextToSelectForCityFields = is_array($valueFromConfig) ? $valueFromConfig : array();
		$value = in_array($key, $convertTextToSelectForCityFields);
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
		$pSortableFieldsList = $this->getInputModelDBFactory()->create(
			InputModelDBFactory::INPUT_FIELD_CONFIG, null, true);
		$pSortableFieldsList->setHtmlType($htmlType);
		if (! $isShow) {
			return $pSortableFieldsList;
		}
		$pFieldsCollection = $this->getFieldsCollection();
		$fieldNames = [];

		if (is_array($module)) {
			foreach ($module as $submodule) {
				$newFields = $pFieldsCollection->getFieldsByModule($submodule);
				$fieldNames = array_merge($fieldNames, $newFields);
			}
		} else {
			$fieldNames = $pFieldsCollection->getFieldsByModule($module);
		}

		$fieldNamesArray = [];
		$pFieldsCollectionUsedFields = new FieldsCollection;

		foreach ($fieldNames as $pField) {
			$fieldNamesArray[$pField->getName()] = $pField->getAsRow();
			$pFieldsCollectionUsedFields->addField($pField);
		}
		$pSortableFieldsList->setValuesAvailable($fieldNamesArray);
		$fields = $this->getValue(DataFormConfiguration::FIELDS) ?? [];
		$pSortableFieldsList->setValue($fields);
		$pInputModelIsFilterable = $this->getInputModelIsFilterable();
		$pInputModelIsHidden = $this->getInputModelIsHidden();
		$pInputModelConvertInputTextToSelectCityField = $this->getInputModelConvertInputTextToSelectCityField();
		$pInputModelIsAvailableOptions = $this->getInputModelAvailableOptions();
		$pSortableFieldsList->addReferencedInputModel($pInputModelIsFilterable);
		$pSortableFieldsList->addReferencedInputModel($pInputModelIsHidden);
		$pSortableFieldsList->addReferencedInputModel($pInputModelConvertInputTextToSelectCityField);
		$pSortableFieldsList->addReferencedInputModel($pInputModelIsAvailableOptions);
		$pSortableFieldsList->addReferencedInputModel($this->getInputModelCustomLabel($pFieldsCollectionUsedFields));
		$pSortableFieldsList->addReferencedInputModel($this->getInputModelCustomLabelLanguageSwitch());

		return $pSortableFieldsList;
	}

	/**
	 * @param FieldsCollection $pFieldsCollection
	 * @return InputModelDB
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function getInputModelCustomLabel(FieldsCollection $pFieldsCollection): InputModelDB
	{
		$pDIContainerBuilder = new ContainerBuilder();
		$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pDIContainerBuilder->build();
		$pInputModelBuilder = $pContainer->get(InputModelBuilderCustomLabel::class);
		return $pInputModelBuilder->createInputModelCustomLabel($pFieldsCollection, $this->getValue('customlabel', []));
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

	public function createInputModelShowReferenceEstates()
	{
		$labelShowReferenceEstate = __( 'Reference estates', 'onoffice-for-wp-websites' );

		$pInputModelShowReferenceEstate = $this->getInputModelDBFactory()->create
		( InputModelDBFactory::INPUT_SHOW_REFERENCE_ESTATE, $labelShowReferenceEstate );
		$pInputModelShowReferenceEstate->setHtmlType( InputModelOption::HTML_TYPE_SELECT );
		$pInputModelShowReferenceEstate->setValue( $this->getValue( $pInputModelShowReferenceEstate->getField() ) );
		$pInputModelShowReferenceEstate->setValuesAvailable( self::getListViewReferenceEstates() );

		return $pInputModelShowReferenceEstate;
	}

	/**
	 *
	 * @return array enum values from DB
	 *
	 */

	static public function getListViewReferenceEstates()
	{
		return array(
			DataListView::HIDE_REFERENCE_ESTATE      => __( 'Hide reference estates', 'onoffice-for-wp-websites' ),
			DataListView::SHOW_REFERENCE_ESTATE      => __( 'Show reference estates (alongside others)',
				'onoffice-for-wp-websites' ),
			DataListView::SHOW_ONLY_REFERENCE_ESTATE => __( 'Show only reference estates (filter out all others)',
				'onoffice-for-wp-websites' ),
		);
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
			$pictureTypes = array(
				'Titelbild',
			);
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
		$labelExpose = __('Direct download for PDF exposé', 'onoffice-for-wp-websites');

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
			DataListView::LISTVIEW_TYPE_FAVORITES => __('Favorites List', 'onoffice-for-wp-websites'),
		);
	}


	/**
	 * @return array
	 */

	public function getDataOfSortByInput() {
		$fieldnames   = $this->readFieldnames( onOfficeSDK::MODULE_ESTATE, false );
		$valuePopular = $this->getOnlyDefaultSortByFields( onOfficeSDK::MODULE_ESTATE );
		$data         = [];
		if ( ! empty( $fieldnames ) ) {
			if ( ! empty( $valuePopular ) ) {
				$data['group'][__('Popular', 'onoffice-for-wp-websites')] = $valuePopular;
			}
			$valueAll = array_diff_key( $fieldnames, $valuePopular );
			if ( ! empty( $valueAll ) ) {
				natcasesort( $valueAll );
				$data['group'][__('All', 'onoffice-for-wp-websites')] = $valueAll;
			}
		}

		return $data;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelSortBySelectTwoStandard() {
		$label       = __( 'Sort by', 'onoffice-for-wp-websites' );
		$pInputModel = $this->getInputModelDBFactory()->create
		( InputModelDBFactory::INPUT_SORTBY, $label, true );
		$pInputModel->setHtmlType( InputModelOption::HTML_TYPE_SELECT_TWO );
		$pInputModel->setIsMulti( false );
		$pInputModel->setValuesAvailable( $this->getDataOfSortByInput() );
		$value = $this->getValue( DataListView::SORT_BY_STANDARD_VALUES );
		if ( $value == null ) {
			$value = [];
		}
		$pInputModel->setValue( $value );

		return $pInputModel;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelSortBySelectTwo()
	{
		$label       = __( 'Sort by', 'onoffice-for-wp-websites' );
		$pInputModel = $this->getInputModelDBFactory()->create
		( InputModelDBFactory::INPUT_SORT_BY_SELECT_TWO, $label, true );
		$pInputModel->setHtmlType( InputModelOption::HTML_TYPE_SELECT_TWO );
		$pInputModel->setIsMulti( true );
		$pInputModel->setValuesAvailable( $this->getDataOfSortByInput() );
		$value = $this->getValue( DataListView::SORT_BY_USER_VALUES );
		if ( $value == null ) {
			$value = [];
		}
		$pInputModel->setValue( $value );

		return $pInputModel;
	}


	/**
	 * @return array
	 */

	public function getDefaultDataOfMarkedPropertiesSort() {
		return [
			'neu' => __('New', 'onoffice-for-wp-websites'),
			'top_angebot' => __('Top offer', 'onoffice-for-wp-websites'),
			'no_marker' => __('No marker', 'onoffice-for-wp-websites'),
			'kauf' => __('Sold', 'onoffice-for-wp-websites'),
			'miete' => __('Rented', 'onoffice-for-wp-websites'),
			'reserviert' => __('Reserved', 'onoffice-for-wp-websites'),
			'referenz' => __('Reference', 'onoffice-for-wp-websites'),
		];
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
		$pInputModel->setHtmlType(InputModelOption::HTML_TYPE_SELECT_TWO);

		$selectedValue = $this->getValue($pInputModel->getField());
		$pInputModel->setValue($selectedValue);
		$values = $this->getValue(DataListView::SORT_BY_USER_VALUES);
		$sortBySpec = $this->getValue(InputModelDBFactory::INPUT_SORT_BY_USER_DEFINED_DIRECTION);

		if ($values == null) {
			$values = [];
		}
		$fieldnames = $this->readFieldnames(onOfficeSDK::MODULE_ESTATE, false);
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


	/**
	 * @return InputModelDB
	 */

	public function createInputModelSortingSelection()
    {
        $label = __('Sorting', 'onoffice-for-wp-websites');

        $userSortingDirectionValues = [
            '0' => __('Default sort', 'onoffice-for-wp-websites'),
            '1' => __('User selection', 'onoffice-for-wp-websites'),
            '' => __('Random order', 'onoffice-for-wp-websites'),
            '2' => __('Marked properties', 'onoffice-for-wp-websites'),
        ];

	    $pInputModel = $this->getInputModelDBFactory()->create( InputModelDBFactory::INPUT_SORT_BY_SETTING, $label );
	    $pInputModel->setHtmlType( InputModelOption::HTML_TYPE_SELECT );
	    $pInputModel->setValue( $this->getValue( $pInputModel->getField() ) ?? '0' );
	    $pInputModel->setValuesAvailable( $userSortingDirectionValues );

	    return $pInputModel;
    }

	/**
	 * @return FieldsCollection
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	protected function getFieldsCollection(): FieldsCollection
	{
		$pFieldsCollectionBuilder = $this->_pContainer->get(FieldsCollectionBuilderShort::class);
		$pFieldsCollection = new FieldsCollection();

		$pFieldsCollectionBuilder
			->addFieldsAddressEstate($pFieldsCollection)
			->addFieldsEstateDecoratorReadAddressBackend($pFieldsCollection)
			->addFieldsEstateGeoPosisionBackend($pFieldsCollection);
		return $pFieldsCollection;
	}

	/**
	 * @return InputModelDB
	 */
	public function getInputModelCustomLabelLanguageSwitch(): InputModelDB
	{
		$pInputModel = new InputModelDB('customlabel_newlang',
			__('Add custom label language', 'onoffice-for-wp-websites'));
		$pInputModel->setTable('language-custom-label');
		$pInputModel->setField('language');

		$pLanguageReader = new InstalledLanguageReader;
		$languages = ['' => __('Choose Language', 'onoffice-for-wp-websites')]
			+ $pLanguageReader->readAvailableLanguageNamesUsingNativeName();
		$pInputModel->setValuesAvailable(array_diff_key($languages, [get_locale() => []]));
		$pInputModel->setValueCallback(function (InputModelDB $pInputModel) {
			$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_SELECT);
			$pInputModel->setLabel(__('Add custom label language', 'onoffice-for-wp-websites'));
		});
		return $pInputModel;
	}

	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelShowMap()
	{
		$labelShowMap = __('Show estate map', 'onoffice-for-wp-websites');

		$pInputModelShowMap = $this->getInputModelDBFactory()->create
		(InputModelDBFactory::INPUT_SHOW_MAP, $labelShowMap);
		$pInputModelShowMap->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelShowMap->setValue($this->getValue('show_map') ?? true);
		$pInputModelShowMap->setValuesAvailable(1);

		return $pInputModelShowMap;
	}

	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelShowPriceOnRequest()
	{
		$labelShowPriceOnRequest = __('Show price on request', 'onoffice-for-wp-websites');

		$pInputModelShowPriceOnRequest = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_SHOW_PRICE_ON_REQUEST, $labelShowPriceOnRequest);
		$pInputModelShowPriceOnRequest->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelShowPriceOnRequest->setValue($this->getValue('show_price_on_request'));
		$pInputModelShowPriceOnRequest->setValuesAvailable(1);

		return $pInputModelShowPriceOnRequest;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelMarkedPropertiesSort() {
		$label = __( 'Sequence', 'onoffice-for-wp-websites' );
		$pInputModel = $this->getInputModelDBFactory()->create
		(InputModelDBFactory::INPUT_MARKED_PROPERTIES_SORT, $label, true);
		$pInputModel->setHtmlType(InputModelOption::HTML_TYPE_SORTABLE_TAGS);
		$defaultData = $this->getDefaultDataOfMarkedPropertiesSort();
		$pInputModel->setValuesAvailable($defaultData);
		$value = $this->getValue(DataListView::SORT_MARKED_PROPERTIES);

		$markedPropertiesSortableDefaultData = [];
		
		if ($value !== null) {
			foreach (explode(',', $value) as $value) {
				if (isset($defaultData[$value])) {
					$markedPropertiesSortableDefaultData[$value] = $defaultData[$value];
				}
			}
		}
		$values = !empty($markedPropertiesSortableDefaultData) ? $markedPropertiesSortableDefaultData : $defaultData;
		$pInputModel->setValue( $values );

		return $pInputModel;
	}

	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelSortByTags() {
		$label = __( 'Sort criteria within same selection', 'onoffice-for-wp-websites' );
		$pInputModel = $this->getInputModelDBFactory()->create
		( InputModelDBFactory::INPUT_SORT_BY_TAGS, $label, true );
		$pInputModel->setHtmlType( InputModelOption::HTML_TYPE_SELECT_TWO );
		$pInputModel->setIsMulti( false );
		$pInputModel->setValuesAvailable( $this->getDataOfSortByInput() );
		$value = $this->getValue( DataListView::SORT_BY_TAGS );
		if (is_null($value)) {
			$value = [];
		}
		$pInputModel->setValue( $value );

		return $pInputModel;
	}

	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelSortByTagsDirection()
	{
		$labelSortByTagsDirection = __('Sort direction', 'onoffice-for-wp-websites');
		$pInputModelSortByTagsDirection = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_SORT_ORDER_BY_TAGS, $labelSortByTagsDirection);
		$pInputModelSortByTagsDirection->setHtmlType(InputModelOption::HTML_TYPE_SELECT);
		$pInputModelSortByTagsDirection->setValuesAvailable(array(
			'ASC' => __('Ascending', 'onoffice-for-wp-websites'),
			'DESC' => __('Descending', 'onoffice-for-wp-websites'),
		));
		$pInputModelSortByTagsDirection->setValue($this->getValue($pInputModelSortByTagsDirection->getField()));

		return $pInputModelSortByTagsDirection;
	}

	/**
	 * @param $module
	 * @param string $htmlType
	 * @return InputModelDB
	 */

	public function createSearchFieldForFieldLists($module, string $htmlType)
	{
		$pInputModelFieldsConfig = $this->getInputModelDBFactory()->create(
			InputModelDBFactory::INPUT_FIELD_CONFIG, null, true);

		$pFieldsCollection = $this->getFieldsCollection();
		$fieldNames = $pFieldsCollection->getFieldsByModule($module);

		$fieldNamesArray = [];
		$pFieldsCollectionUsedFields = new FieldsCollection;

		foreach ($fieldNames as $pField) {
			$fieldNamesArray[$pField->getName()] = $pField->getAsRow();
			$pFieldsCollectionUsedFields->addField($pField);
		}

		$pInputModelFieldsConfig->setValuesAvailable($this->groupByContent($fieldNamesArray));
		$pInputModelFieldsConfig->setHtmlType($htmlType);
		$pInputModelFieldsConfig->setValue($this->getValue(DataListView::FIELDS) ?? []);

		return $pInputModelFieldsConfig;
	}
}
