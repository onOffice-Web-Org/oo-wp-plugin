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
	 * In unit list settings, the field options "Filterable", "Hidden"
	 * and "Reduce values according to selected filter" has been removed.
	 * @param string $module
	 * @param string $htmlType
	 * @param bool $isShow
	 * @return InputModelDB
	 */
	public function createSortableFieldList($module, $htmlType, bool $isShow = true): InputModelDB
	{
		return parent::createSortableFieldList($module, $htmlType, false);
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
}
