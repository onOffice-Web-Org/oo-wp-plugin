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
use onOffice\WPlugin\Model\InputModelDB;
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
		'bundesland',
		'objektnr_extern',
		'wohnflaeche',
		'grundstuecksflaeche',
		'nutzflaeche',
		'anzahl_zimmer',
		'anzahl_badezimmer',
		'kaufpreis',
		'kaltmiete',
		'objektbeschreibung',
		'lage',
		'ausstatt_beschr',
		'sonstige_angaben'
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
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelRandomOrder()
	{
		$labelShowStatus = __('Random Order', 'onoffice-for-wp-websites');

		$pInputModelShowStatus = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_RANDOM_ORDER, $labelShowStatus);
		$pInputModelShowStatus->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelShowStatus->setValue($this->getValue('random'));
		$pInputModelShowStatus->setValuesAvailable(1);

		return $pInputModelShowStatus;
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
}
