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

namespace onOffice\WPlugin\Gui;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Renderer\InputModelRenderer;
use onOffice\WPlugin\Utility\__String;
use onOffice\WPlugin\Utility\HtmlIdGenerator;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

abstract class AdminPageAjax
	extends AdminPageBase
{
	/** */
	const ENQUEUE_DATA_MERGE = 'merge';

	/**
	 *
	 * Entry point for AJAX.
	 * Method should end with wp_die().
	 *
	 * @url https://codex.wordpress.org/AJAX_in_Plugins
	 *
	 */

	abstract public function ajax_action();


	/**
	 *
	 */

	public function checkForms()
	{
		$pCurrentScreen = get_current_screen();

		if ($pCurrentScreen !== null &&
			__String::getNew($pCurrentScreen->id)->contains('onoffice') &&
			is_admin())
		{
			$this->buildForms();
		}
	}


	/**
	 *
	 * @param FormModel $pFormModel
	 * @param string $position
	 * @param InputModelRenderer $pInputModelRenderer
	 *
	 */

	protected function createMetaBoxByForm(FormModel $pFormModel,
		$position = 'left', InputModelRenderer $pInputModelRenderer = null)
	{
		$screenId = get_current_screen()->id;
		$formId = $pFormModel->getGroupSlug();
		$formIdHtmlFriendly = HtmlIdGenerator::generateByString($formId);
		$formLabel = $pFormModel->getLabel();

		if ($pInputModelRenderer === null) {
			$pInputModelRenderer = new InputModelRenderer($pFormModel);
		}

		$callback =  array($pInputModelRenderer, 'buildForAjax');
		add_meta_box($formIdHtmlFriendly, $formLabel, $callback, $screenId, $position, 'default' );
	}


	/**
	 *
	 */

	abstract protected function buildForms();


	/**
	 *
	 * @return array
	 *
	 */

	public function getEnqueueData()
	{
		return array();
	}


	/**
	 *
	 * @param string $module
	 * @param bool $extraFields
	 * @return array
	 *
	 */

	protected function readFieldnamesByContent($module, $extraFields = false)
	{
		$pFieldnames = new Fieldnames();
		$pFieldnames->loadLanguage();
		$modus = null;

		if ($module == onOfficeSDK::MODULE_ESTATE) {
			$modus = \onOffice\WPlugin\GeoPosition::MODUS_TYPE_ADMIN_INTERFACE;
		}
		elseif ($module == onOfficeSDK::MODULE_SEARCHCRITERIA) {
			$modus = \onOffice\WPlugin\GeoPosition::MODUS_TYPE_ADMIN_SEARCH_CRITERIA;
		}

		$fieldnames = $pFieldnames->getFieldList($module, $extraFields, $extraFields, $modus);
		$resultByContent = array();
		$categories = array();

		foreach ($fieldnames as $key => $properties) {
			$content = $properties['content'];
			$categories []= $content;
			$label = $properties['label'];
			$resultByContent[$content][$key] = $label;
		}

		foreach ($categories as $category) {
			natcasesort($resultByContent[$category]);
		}

		return $resultByContent;
	}
}
