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

use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigEstate;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelLabel;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Record\RecordManagerReadListViewEstate;
use function __;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\Types\FieldsCollection;
use DI\ContainerBuilder;
use onOffice\WPlugin\WP\InstalledLanguageReader;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Model\InputModelBuilder\InputModelBuilderCustomLabel;
use DI\DependencyException;
use DI\NotFoundException;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class FormModelBuilderDBEstateUnitListSettings
	extends FormModelBuilderDBEstateListSettings
{
	/** @var string[] */
	private static $_defaultFields = [
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
	];


	/**
	 *
	 * @param string $pageSlug
	 * @param int $listViewId
	 * @return FormModel
	 *
	 */

	public function generate(string $pageSlug, $listViewId = null): FormModel
	{
		if ($listViewId !== null) {
			$pRecordReadManager = new RecordManagerReadListViewEstate();
			$values = $pRecordReadManager->getRowById($listViewId);
			$this->setValues($values);
		} else {
			$this->setValues(array(
				DataListView::FIELDS => self::$_defaultFields,
			));
		}

		$pFormModel = new FormModel();
		$pFormModel->setLabel(__('Unit List', 'onoffice-for-wp-websites'));
		$pFormModel->setGroupSlug('onoffice-unitlist-settings');
		$pFormModel->setPageSlug($pageSlug);

		return $pFormModel;
	}

	/**
	 * In unit list settings, the field options "Show in search", "Hidden"
	 * and "Hide empty values from onOffice enterprise" has been removed.
	 * @param string $module
	 * @param string $htmlType
	 * @param bool $isShow
	 * @return InputModelDB
	 */
	public function createSortableFieldList($module, $htmlType, bool $isShow = true): InputModelDB
	{
		$pSortableFieldsList = parent::createSortableFieldList($module, $htmlType, false);
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
		$pSortableFieldsList->addReferencedInputModel($this->getInputModelCustomLabel($pFieldsCollectionUsedFields));
		$pSortableFieldsList->addReferencedInputModel($this->getInputModelCustomLabelLanguageSwitch());

		return $pSortableFieldsList;
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

		$codes           = '[oo_estate units="'.$listName.'" view="..."]';
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

		$codes           = '[oo_estate units="'.$listName.'" view="..."]';
		$pInputModeLabel = new InputModelLabel( '', $codes );
		$pInputModeLabel->setHtmlType( InputModelBase::HTML_TYPE_BUTTON );

		return $pInputModeLabel;
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
			->addFieldsAddressEstate($pFieldsCollection);
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
	 * @param $module
	 * @param string $htmlType
	 * @return InputModelDB
	 *
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
