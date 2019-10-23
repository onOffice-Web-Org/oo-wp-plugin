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

use DI\ContainerBuilder;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Types\FieldTypes;
use const ONOFFICE_DI_CONFIG_PATH;
use function __;
use function esc_html;
use function esc_html__;

/**
 *
 */

class InputFieldComplexSortableDetailListContentDefault
{
	/** @var int */
	private static $_id = 0;

	/** @var array */
	private $_extraInputModels = [];



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
	 * @param bool $isDummy
	 * @param string $type
	 *
	 */

	public function render(string $key, bool $isDummy, string $type = null)
	{
		$pFormModel = new FormModel();

		foreach ($this->_extraInputModels as $pInputModel) {
			if (!in_array($type, [FieldTypes::FIELD_TYPE_MULTISELECT, FieldTypes::FIELD_TYPE_SINGLESELECT]) &&
				$pInputModel->getField() == 'availableOptions')
			{
				continue;
			}

			$pInputModel->setIgnore($isDummy);
			$callbackValue = $pInputModel->getValueCallback();
			$module = $pInputModel->getOoModule();

			if ($callbackValue !== null) {
				call_user_func($callbackValue, $pInputModel, $key, $module);
			}

			$pFormModel->addInputModel($pInputModel);
		}

		$pDIContainerBuilder = new ContainerBuilder();
		$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pDIContainerBuilder->build();
		/* @var $pInputModelRenderer InputModelRenderer */
		$pInputModelRenderer = $pContainer->get(InputModelRenderer::class);
		echo '<p class="wp-clearfix"><label class="howto">'.esc_html__('Key of Field:', 'onoffice')
				.'&nbsp;</label><span class="menu-item-settings-name">'.esc_html($key).'</span></p>';

		$pInputModelRenderer->buildForAjax($pFormModel);

		echo '<a class="item-delete-link submitdelete">'.__('Delete', 'onoffice').'</a>';
	}


	/** @return array */
	public function getExtraInputModels(): array
		{ return $this->_extraInputModels; }

	/** @param InputModelBase $pInputModel */
	public function addExtraInputModel(InputModelBase $pInputModel)
		{ $this->_extraInputModels []= $pInputModel; }

	/** @var InputModelBase[] $extraInputModels */
	public function setExtraInputModels(array $extraInputModels)
		{ $this->_extraInputModels = $extraInputModels; }
}
