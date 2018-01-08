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

namespace onOffice\WPlugin\Renderer;

use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModelBase;

/**
 *
 */

class InputFieldComplexSortableDetailListContentForm
	implements InputFieldComplexSortableDetailListContentBase
{
	/** @var int */
	private static $_id = 0;

	/** @var InputModelBase[] */
	private $_extraInputModels = array();

	/**
	 *
	 */

	public function __construct()
	{
		self::$_id++;
	}


	/**
	 *
	 * @param string $key
	 * @param bool $dummy
	 *
	 */

	public function render($key, $dummy)
	{
		$dummyText = $dummy ? ' data-onoffice-ignore="true"' : '';
		$id = (int)self::$_id;

		$pFormModel = new FormModel();

		foreach ($this->_extraInputModels as $pInputModel) {
			$pInputModel->setValuesAvailable($key);
			$pInputModel->setValue(false);
			$pFormModel->addInputModel($pInputModel);
		}

		$pInputModelRenderer = new InputModelRenderer($pFormModel);

		echo '<p class="description">'.esc_html__('Key of Field:', 'onoffice')
			.' <span class="menu-item-settings-name">'.esc_html($key).'</span></p>';

		echo '<p class="description">';
		$pInputModelRenderer->buildForAjax();
		echo '</p>';
		echo '<p class="description"><label for="indName'.$id.'">'
			.__('Use Individual Name:').'</label><input type="checkbox" '
			.'name="useIndividualName['.esc_html($key).']" value="1" id="indName'.$id.'"'.$dummyText.'></p>';
		echo '<p class="description">'.__('Individual Name:').'<input type="text" '
			.'name="individualName['.esc_html($key).']"'.$dummyText.'></p>';
		echo '<a class="item-delete-link submitdelete">'.__('Delete', 'onoffice').'</a>';
	}

	/** @return InputModelBase[] */
	public function getExtraInputModels()
		{ return $this->_extraInputModels; }

	/** @param InputModelBase $pInputModel */
	public function addExtraInputModel(InputModelBase $pInputModel)
		{ $this->_extraInputModels []= $pInputModel; }

	/** @var InputModelBase[] $extraInputModels */
	public function setExtraInputModels(array $extraInputModels)
		{ $this->_extraInputModels = $extraInputModels; }
}