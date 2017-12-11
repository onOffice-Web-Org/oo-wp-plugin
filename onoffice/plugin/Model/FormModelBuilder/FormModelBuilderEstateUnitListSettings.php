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

use onOffice\WPlugin\Model;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class FormModelBuilderEstateUnitListSettings
	extends FormModelBuilderEstateListSettings
{
	/**
	 *
	 * @param int $listViewId
	 * @return \onOffice\WPlugin\Model\FormModel
	 *
	 */

	public function generate($listViewId = null)
	{
		if ($listViewId !== null)
		{
			$pRecordReadManager = new \onOffice\WPlugin\Record\RecordManagerReadListView();
			$values = $pRecordReadManager->getRowById($listViewId);
			$this->setValues($values);
		}

		$pFormModel = new Model\FormModel();
		$pFormModel->setLabel(__('Unit List', 'onoffice'));
		$pFormModel->setGroupSlug('onoffice-unitlist-settings');
		$pFormModel->setPageSlug($this->getPageSlug());

		return $pFormModel;
	}


	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	public function createInputModelRandomOrder()
	{
		$labelShowStatus = __('Random Order', 'onoffice');

		$pInputModelShowStatus = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_RANDOM_ORDER, $labelShowStatus);
		$pInputModelShowStatus->setHtmlType(Model\InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelShowStatus->setValue($this->getValue('random'));
		$pInputModelShowStatus->setValuesAvailable(1);

		return $pInputModelShowStatus;
	}
}
