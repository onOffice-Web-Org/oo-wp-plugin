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
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use onOffice\WPlugin\Gui\AdminPageAjax;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Types\FieldTypes;
use RuntimeException;
use function __;
use function esc_html;
use function esc_html__;
use const ONOFFICE_DI_CONFIG_PATH;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorReadAddress;

/**
 *
 */

class InputFieldComplexSortableDetailListContentDefault
{
	/**
	 * @var InputModelRenderer
	 */
	private InputModelRenderer $inputModelRenderer;

	/**
	 * Initializes a new instance of the class
	 *
	 * @throws RuntimeException|Exception
	 */
	public function __construct()
	{
		try {
            $pDIContainerBuilder = new ContainerBuilder();
            $pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
            $pContainer = $pDIContainerBuilder->build();
            $this->inputModelRenderer = $pContainer->get(InputModelRenderer::class);
        } catch (DependencyException | NotFoundException $e) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception in internal initialization
            throw new RuntimeException('Failed to initialize InputModelRenderer: ' . $e->getMessage(), 0, $e);
        }

	}

	/**
	 * @param string $key
	 * @param bool $isDummy
	 * @param string $type
	 * @param array $extraInputModels
	 * @param bool $isMutiplePage
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws Exception
	 */

	public function render(string $key, bool $isDummy,
		string $type = null, array $extraInputModels = [], bool $isMutiplePage = false)
	{
		$pFormModel = new FormModel();

		foreach ($extraInputModels as $pInputModel) {
			if (!in_array($type, [FieldTypes::FIELD_TYPE_MULTISELECT, FieldTypes::FIELD_TYPE_SINGLESELECT]) &&
				$pInputModel->getField() == 'availableOptions') {
				continue;
			}
			if (($key === 'DSGVOStatus' || $key === 'AGB_akzeptiert' || $key === 'gdprcheckbox') && $pInputModel->getField() === 'hidden_field') {
				continue;
			}

			if ($key !== 'ort' && $pInputModel->getField() == 'convertTextToSelectForCityField' && !$isDummy) {
				continue;
			}
			if ($key !== 'Ort' && $pInputModel->getField() == 'convertInputTextToSelectForField' && !$isDummy) {
				continue;
			}
			if ($pInputModel->getTable() === 'oo_plugin_form_multipage_title') {
				continue;
			}
			if (array_key_exists($key, FieldModuleCollectionDecoratorReadAddress::getNewAddressFields()) && !$isDummy && ($pInputModel->getField() === 'filterable' || $pInputModel->getField() === 'hidden')) {
				continue;
			}
			$pInputModel->setIgnore($isDummy);
			$callbackValue = $pInputModel->getValueCallback();

			if ($callbackValue !== null) {
				call_user_func($callbackValue, $pInputModel, $key, $type);
			}

			if ($isDummy) {
				$pInputModel->setTable(AdminPageAjax::EXCLUDE_FIELD . $pInputModel->getTable());
			} elseif ($isMutiplePage) {
				if (strpos($pInputModel->getTable(), AdminPageAjax::EXCLUDE_FIELD) === 0) {
					$pInputModel->setTable(substr($pInputModel->getTable(), strlen(AdminPageAjax::EXCLUDE_FIELD)));
				}
			}

			$pFormModel->addInputModel($pInputModel);
		}

		echo '<p class="wp-clearfix key-of-field-block"><label class="howto">' . esc_html__('Key of Field:', 'onoffice-for-wp-websites')
				.'&nbsp;</label><span class="menu-item-settings-name">'.esc_html($key).'</span></p>';

		$this->inputModelRenderer->buildForAjax($pFormModel);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_html() in concatenation, __() returns translated string
        echo '<a class="item-delete-link submitdelete oo-delete-button-'.esc_attr($key).'">'.esc_html__('Delete', 'onoffice-for-wp-websites').'</a>';
	}

	/**
	 * Renders input fields for multilingual page titles
	 *
	 * @param array $titleInputModels
	 * @param array $titles
	 * @return void
	 * @throws Exception
	 */
	public function renderTitlesForMultiPage(array $titleInputModels, array $titles):void {
		$pFormModel = new FormModel();

		foreach ($titleInputModels as $pInputModel) {
			if ($pInputModel->getField() === 'value') {
				foreach ($titles as $title) {
					$this->addInputModelToFormModel(clone $pInputModel,$pFormModel, $title);
				}
			} else {
				$this->addInputModelToFormModel($pInputModel, $pFormModel, $titles);
			}
		}
		try {
			$this->inputModelRenderer->buildForAjax($pFormModel);
		} catch (Exception $e) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception in internal rendering
            throw new RuntimeException('Failed to render title for multipage form: ' . $e->getMessage(), 0, $e);
		}
	}


	/**
	 * Add an input model to form model with callback if available
	 *
	 * @param $pInputModel
	 * @param $pFormModel
	 * @param mixed $callbackParam
	 * @return void
	 */
	private function addInputModelToFormModel($pInputModel, $pFormModel, mixed $callbackParam): void
	{
		$callbackValue = $pInputModel->getValueCallback();
		if ($callbackValue !== null) {
			call_user_func($callbackValue, $pInputModel, $callbackParam);
		}
		$pFormModel->addInputModel($pInputModel);
	}
}
