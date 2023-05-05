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
use onOffice\WPlugin\Controller\Exception\UnknownModuleException;
use onOffice\WPlugin\DataFormConfiguration\UnknownFormException;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorGeoPositionBackend;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorInternalAnnotations;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorReadAddress;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Model\ExceptionInputModelMissingField;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelOptionFactoryDetailView;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\ImageTypes;
use onOffice\WPlugin\Types\LinksTypes;
use onOffice\WPlugin\Types\MovieLinkTypes;
use onOffice\WPlugin\Utility\__String;
use function __;
use DI\ContainerBuilder;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Model\InputModelBuilder\InputModelBuilderCustomLabel;
use onOffice\WPlugin\WP\InstalledLanguageReader;
use DI\DependencyException;
use DI\NotFoundException;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 * This class must not use InputModelDB!
 *
 */

class FormModelBuilderEstateDetailSettings
	extends FormModelBuilder
{
	/** @var InputModelOptionFactoryDetailView */
	private $_pInputModelDetailViewFactory = null;

	/** @var DataDetailView */
	private $_pDataDetailView = null;

	/** @var Fieldnames */
	private $_pFieldnames = null;


	/**
	 *
	 */

	public function __construct(Fieldnames $_pFieldnames = null)
	{
		$pFieldCollection = new FieldModuleCollectionDecoratorInternalAnnotations
			(new FieldModuleCollectionDecoratorReadAddress
				(new FieldModuleCollectionDecoratorGeoPositionBackend(new FieldsCollection())));
		$this->_pFieldnames = $_pFieldnames ?? new Fieldnames($pFieldCollection);
		$this->_pFieldnames->loadLanguage();
		$this->setFieldnames($this->_pFieldnames);
	}


	/**
	 *
	 * @return FormModel
	 *
	 */

	public function generate(string $pageSlug): FormModel
	{
		$this->_pInputModelDetailViewFactory = new InputModelOptionFactoryDetailView($pageSlug);
		$pDataDetailViewHandler = new DataDetailViewHandler();
		$this->_pDataDetailView = $pDataDetailViewHandler->getDetailView();

		$pFormModel = new FormModel();
		$pFormModel->setLabel(__('Detail View', 'onoffice-for-wp-websites'));
		$pFormModel->setGroupSlug('onoffice-detailview-settings-main');
		$pFormModel->setPageSlug($pageSlug);

		return $pFormModel;
	}

	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelPictureTypes()
	{
		$allPictureTypes = ImageTypes::getAllImageTypesTranslated();

		$pInputModelPictureTypes = $this->_pInputModelDetailViewFactory->create
			(InputModelOptionFactoryDetailView::INPUT_PICTURE_TYPE, null, true);
		$pInputModelPictureTypes->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelPictureTypes->setValuesAvailable($allPictureTypes);
		$pictureTypes = $this->_pDataDetailView->getPictureTypes();

		if (null == $pictureTypes)
		{
			$pictureTypes = array(
				'Titelbild',
				'Foto',
				'Foto_gross',
				'Panorama',
				'Grundriss',
				'Lageplan',
				'Epass_Skala',
			);
		}

		$pInputModelPictureTypes->setValue($pictureTypes);

		return $pInputModelPictureTypes;
	}


	/**
	 *
	 * @return InputModelDB
	 * @throws ExceptionInputModelMissingField
	 */

	public function createInputAccessControl() {
		$allAccessControl = __('Restrict access to reference estates (404 Not Found)', 'onoffice-for-wp-websites');

		$pInputModelAccessControl = $this->_pInputModelDetailViewFactory->create( InputModelOptionFactoryDetailView::INPUT_ACCESS_CONTROL,
			$allAccessControl);
		$pInputModelAccessControl->setHtmlType( InputModelOption::HTML_TYPE_CHECKBOX );
		$pInputModelAccessControl->setValuesAvailable( 'Access_Control' );
		$accessControl = $this->_pDataDetailView->hasDetailView();
		$pInputModelAccessControl->setValue( $accessControl );

		return $pInputModelAccessControl;
	}

	/**
	 *
	 * @return InputModelDB
	 * @throws ExceptionInputModelMissingField
	 */

	public function createInputRestrictAccessControl()
	{
		$allRestrictAccessControl = __( 'Restrict access to reference estates (404 Not Found)',
			'onoffice-for-wp-websites' );

		$pInputModelRestrictAccessControl = $this->_pInputModelDetailViewFactory->create( InputModelOptionFactoryDetailView::INPUT_RESTRICT_ACCESS_CONTROL,
			$allRestrictAccessControl );
		$pInputModelRestrictAccessControl->setHtmlType( InputModelOption::HTML_TYPE_CHECKBOX );
		$pInputModelRestrictAccessControl->setValuesAvailable( 'Restrict_Access_Control' );
		$restrictAccessControl = $this->_pDataDetailView->getViewRestrict();
		$pInputModelRestrictAccessControl->setValue( $restrictAccessControl );

		return $pInputModelRestrictAccessControl;
	}

	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelExpose()
	{
		$labelExpose = __('Direct download for PDF exposé', 'onoffice-for-wp-websites');

		$pInputModelExpose = $this->_pInputModelDetailViewFactory->create
			(InputModelOptionFactoryDetailView::INPUT_EXPOSE, $labelExpose);
		$pInputModelExpose->setHtmlType(InputModelOption::HTML_TYPE_SELECT);
		$exposes = array('' => '') + $this->readExposes();
		$pInputModelExpose->setValuesAvailable($exposes);
		$pInputModelExpose->setValue($this->_pDataDetailView->getExpose());

		return $pInputModelExpose;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelMovieLinks()
	{
		$labelMovieLinks = __('Movie Links', 'onoffice-for-wp-websites');

		$pInputModelMedia = $this->_pInputModelDetailViewFactory->create
			(InputModelOptionFactoryDetailView::INPUT_MOVIE_LINKS, $labelMovieLinks);
		$pInputModelMedia->setHtmlType(InputModelOption::HTML_TYPE_SELECT);
		$options = array(
			MovieLinkTypes::MOVIE_LINKS_NONE => __('Deactivated', 'onoffice-for-wp-websites'),
			MovieLinkTypes::MOVIE_LINKS_LINK => __('Link', 'onoffice-for-wp-websites'),
			MovieLinkTypes::MOVIE_LINKS_PLAYER => __('Player', 'onoffice-for-wp-websites'),
		);
		$pInputModelMedia->setValuesAvailable($options);
		$pInputModelMedia->setValue($this->_pDataDetailView->getMovieLinks());

		return $pInputModelMedia;
	}

	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelOguloLinks()
	{
		$labelOguloLinks = __('Ogulo-Links', 'onoffice-for-wp-websites');

		$pInputModelMedia = $this->_pInputModelDetailViewFactory->create
		(InputModelOptionFactoryDetailView::INPUT_OGULO_LINKS, $labelOguloLinks);
		$pInputModelMedia->setHtmlType(InputModelOption::HTML_TYPE_SELECT);
		$options = array(
			LinksTypes::LINKS_DEACTIVATED => __('Deactivated', 'onoffice-for-wp-websites'),
			LinksTypes::LINKS_LINK => __('Link', 'onoffice-for-wp-websites'),
			LinksTypes::LINKS_EMBEDDED => __('Embedded', 'onoffice-for-wp-websites'),
		);
		$pInputModelMedia->setValuesAvailable($options);
		$pInputModelMedia->setValue($this->_pDataDetailView->getOguloLinks());

		return $pInputModelMedia;
	}

	/**
	 *
	 * @return InputModelDB
	 *
	 */
	public function createInputModelObjectLinks()
	{
		$labelObjectLinks = __('Object-Links', 'onoffice-for-wp-websites');

		$pInputModelMedia = $this->_pInputModelDetailViewFactory->create
		(InputModelOptionFactoryDetailView::INPUT_OBJECT_LINKS, $labelObjectLinks);
		$pInputModelMedia->setHtmlType(InputModelOption::HTML_TYPE_SELECT);
		$options = array(
			LinksTypes::LINKS_DEACTIVATED => __('Deactivated', 'onoffice-for-wp-websites'),
			LinksTypes::LINKS_LINK => __('Link', 'onoffice-for-wp-websites'),
			LinksTypes::LINKS_EMBEDDED => __('Embedded', 'onoffice-for-wp-websites'),
		);
		$pInputModelMedia->setValuesAvailable($options);
		$pInputModelMedia->setValue($this->_pDataDetailView->getObjectLinks());

		return $pInputModelMedia;
	}

	/**
	 *
	 * @return InputModelDB
	 *
	 */
	public function createInputModelLinks()
	{
		$labelLinks = __('Links', 'onoffice-for-wp-websites');

		$pInputModelMedia = $this->_pInputModelDetailViewFactory->create
		(InputModelOptionFactoryDetailView::INPUT_LINKS, $labelLinks);
		$pInputModelMedia->setHtmlType(InputModelOption::HTML_TYPE_SELECT);
		$options = array(
			LinksTypes::LINKS_DEACTIVATED => __('Deactivated', 'onoffice-for-wp-websites'),
			LinksTypes::LINKS_LINK => __('Link', 'onoffice-for-wp-websites'),
			LinksTypes::LINKS_EMBEDDED => __('Embedded', 'onoffice-for-wp-websites'),
		);
		$pInputModelMedia->setValuesAvailable($options);
		$pInputModelMedia->setValue($this->_pDataDetailView->getLinks());

		return $pInputModelMedia;
	}


	/**
	 *
	 * @param string $category
	 * @param array $fieldNames
	 * @param string $categoryLabel
	 * @return InputModelDB
	 *
	 */

	public function createInputModelFieldsConfigByCategory($category, $fieldNames, $categoryLabel)
	{
		$pInputModelFieldsConfig = new InputModelOption
			(null, $category, null, InputModelDBFactory::INPUT_FIELD_CONFIG);
		$pInputModelFieldsConfig->setIsMulti(true);

		$pInputModelFieldsConfig->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX_BUTTON);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);
		$pInputModelFieldsConfig->setId($category);
		$pInputModelFieldsConfig->setLabel($categoryLabel);
		$fields = $this->getValue(DataListView::FIELDS);

		if (null == $fields)
		{
			$fields = array();
		}

		$pInputModelFieldsConfig->setValue($fields);

		return $pInputModelFieldsConfig;
	}

	/**
	 *
	 * @param $module
	 * @param $htmlType
	 * @return InputModelOption
	 * @throws UnknownModuleException
	 * @throws ExceptionInputModelMissingField
	 */

	public function createSortableFieldList($module, $htmlType)
	{
		$fields = [];

		if ($module == onOfficeSDK::MODULE_ESTATE) {
			$pInputModelFieldsConfig = $this->_pInputModelDetailViewFactory->create
				(InputModelOptionFactoryDetailView::INPUT_FIELD_CONFIG, null, true);
			$fields = $this->_pDataDetailView->getFields();
		} elseif ($module == onOfficeSDK::MODULE_ADDRESS) {
			$pInputModelFieldsConfig = $this->_pInputModelDetailViewFactory->create
				(InputModelOptionFactoryDetailView::INPUT_FIELD_CONTACTDATA_ONLY, null, true);
			$fields = $this->_pDataDetailView->getAddressFields();
		} else {
			throw new UnknownModuleException();
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
		$pInputModelFieldsConfig->setHtmlType($htmlType);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNamesArray);
		$pInputModelFieldsConfig->setValue($fields);
		$pInputModelFieldsConfig->addReferencedInputModel($this->getInputModelCustomLabel($pFieldsCollectionUsedFields));
		$pInputModelFieldsConfig->addReferencedInputModel($this->getInputModelCustomLabelLanguageSwitch());
		return $pInputModelFieldsConfig;
	}

	/**
	 * @param string $field
	 * @return InputModelOption
	 * @throws ExceptionInputModelMissingField
	 */
	public function createInputModelTemplate(string $field = InputModelOptionFactoryDetailView::INPUT_TEMPLATE)
	{
		$labelTemplate = __('Template', 'onoffice-for-wp-websites');
		$pInputModelTemplate = $this->_pInputModelDetailViewFactory->create($field, $labelTemplate);
		$pInputModelTemplate->setHtmlType(InputModelOption::HTML_TYPE_TEMPLATE_LIST);
		$pInputModelTemplate->setValuesAvailable($this->readTemplatePaths('estate'));
		$pInputModelTemplate->setValue($this->getTemplateValueByField($field));

		return $pInputModelTemplate;
	}
	/**
	 *
	 * @param string $field
	 * @return string
	 *
	 */

	private function getTemplateValueByField(string $field): string
	{
		switch ($field) {
			case InputModelOptionFactoryDetailView::INPUT_TEMPLATE:
				return $this->_pDataDetailView->getTemplate();
			default:
				return '';
		}
	}

	/**
	 *
	 * @return InputModelDB
	 *
	 * @throws UnknownFormException
	 * @throws ExceptionInputModelMissingField
	 */

	public function createInputModelShortCodeForm()
	{

		$labelShortCodeForm = __('Select Contact Form', 'onoffice-for-wp-websites');
		$pInputModelShortCodeForm = $this->_pInputModelDetailViewFactory->create
		(InputModelOptionFactoryDetailView::INPUT_SHORT_CODE_FORM, $labelShortCodeForm);
		$pInputModelShortCodeForm->setHtmlType(InputModelOption::HTML_TYPE_SELECT);
		$nameShortCodeForms = array('' => __('No Contact Form', 'onoffice-for-wp-websites')) + $this->readNameShortCodeForm();
		$pInputModelShortCodeForm->setValuesAvailable($nameShortCodeForms);

		$pInputModelShortCodeForm->setValue($this->_pDataDetailView->getShortCodeForm());

		return $pInputModelShortCodeForm;
	}

	/**
	 * @return InputModelOption
	 * @throws ExceptionInputModelMissingField
	 */

	public function createInputModelShowStatus()
	{
		$labelShowStatus = __('Show Estate Status', 'onoffice-for-wp-websites');
		$pInputModelShowStatus = $this->_pInputModelDetailViewFactory->create
		(InputModelOptionFactoryDetailView::INPUT_SHOW_STATUS, $labelShowStatus);
		$pInputModelShowStatus->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelShowStatus->setValue($this->_pDataDetailView->getShowStatus());
		$pInputModelShowStatus->setValuesAvailable(1);

		return $pInputModelShowStatus;
	}

	/**
	 *
	 * @return array
	 *
	 * @throws UnknownFormException
	 */

	protected function readNameShortCodeForm()
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
	
	/**
	 *
	 * @param string $category
	 * @param array $fieldNames
	 * @param string $categoryLabel
	 * @return InputModelDB
	 *
	 */

	public function createButtonModelFieldsConfigByCategory($category, $fieldNames, $categoryLabel)
	{
		$pInputModelFieldsConfig = new InputModelOption
			(null, $category, null, InputModelDBFactory::INPUT_FIELD_CONFIG);
		$pInputModelFieldsConfig->setIsMulti(true);

		$pInputModelFieldsConfig->setHtmlType(InputModelBase::HTML_TYPE_BUTTON_FIELD);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);
		$pInputModelFieldsConfig->setId($category);
		$pInputModelFieldsConfig->setLabel($categoryLabel);
		$fields = $this->getValue(DataListView::FIELDS);
		if (null == $fields)
		{
			$fields = array_merge(
				$this->_pDataDetailView->getAddressFields(),
				$this->_pDataDetailView->getFields()
			);
		}

		$pInputModelFieldsConfig->setValue($fields);

		return $pInputModelFieldsConfig;
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
	 * @return FieldsCollection
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function getFieldsCollection(): FieldsCollection
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();

		$pFieldsCollectionBuilder = $pContainer->get(FieldsCollectionBuilderShort::class);
		$pFieldsCollection = new FieldsCollection();

		$pFieldsCollectionBuilder
			->addFieldsAddressEstate($pFieldsCollection)
			->addFieldsAddressEstateWithRegionValues($pFieldsCollection)
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

	public function createInputModelShowPriceOnRequest()
	{
		$labelShowPriceOnRequest = __('Show price on request', 'onoffice-for-wp-websites');

		$pInputModelShowPriceOnRequest = $this->_pInputModelDetailViewFactory->create
		(InputModelOptionFactoryDetailView::INPUT_SHOW_PRICE_ON_REQUEST, $labelShowPriceOnRequest);
		$pInputModelShowPriceOnRequest->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelShowPriceOnRequest->setValue($this->_pDataDetailView->getShowPriceOnRequest());
		$pInputModelShowPriceOnRequest->setValuesAvailable(1);

		return $pInputModelShowPriceOnRequest;
	}
}
