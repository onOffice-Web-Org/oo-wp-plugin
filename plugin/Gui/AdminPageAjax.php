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

use onOffice\WPlugin\API\APIClientCredentialsException;
use onOffice\WPlugin\Field\FieldModuleCollection;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Renderer\InputModelRenderer;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Utility\__String;
use onOffice\WPlugin\Utility\HtmlIdGenerator;
use function __;
use function add_meta_box;
use function get_current_screen;
use function is_admin;
use function wp_die;


/**
 *
 */

abstract class AdminPageAjax
	extends AdminPageBase
{
	/** */
	const ENQUEUE_DATA_MERGE = 'merge';

	/** */
	const EXCLUDE_FIELD = 'exclude';

	/**
	 *
	 * Entry point for AJAX.
	 * Method should end with wp_die().
	 *
	 * @url https://codex.wordpress.org/AJAX_in_Plugins
	 *
	 */

	abstract public function save_form();


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
			try {
				$this->buildForms();
			} catch (APIClientCredentialsException $pCredentialsException) {
				$label = __('login credentials', 'onoffice-for-wp-websites');
				$loginCredentialsLink = sprintf('<a href="admin.php?page=onoffice-settings">%s</a>', $label);
				/* translators: %s will be replaced with the link to the login credentials page. */
				wp_die(sprintf(__('It looks like you did not enter any valid API credentials. '
					.'Please go back and review your %s.', 'onoffice-for-wp-websites'), $loginCredentialsLink), 'onOffice plugin');
			}
		}
	}

	/**
	 * @param FormModel $pFormModel
	 * @param string $position
	 */
	protected function createMetaBoxByForm(FormModel $pFormModel, string $position = 'left')
	{
		$screenId = get_current_screen()->id;
		$formId = $pFormModel->getGroupSlug();
		$formIdHtmlFriendly = HtmlIdGenerator::generateByString($formId);
		$formLabel = $pFormModel->getLabel();


		$callback = function() use ($pFormModel) {
			/* @var $pInputModelRenderer InputModelRenderer */
			$pInputModelRenderer = $this->getContainer()->get(InputModelRenderer::class);
			$pInputModelRenderer->buildForAjax($pFormModel);
		};
		add_meta_box($formIdHtmlFriendly, $formLabel, $callback, $screenId, $position, 'default');
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

	public function getEnqueueData(): array
	{
		return [];
	}


	/**
	 *
	 * @deprecated use FieldsCollectionToContentFieldLabelArrayConverter instead
	 *
	 * @param string $module
	 * @param FieldModuleCollection $pFieldsCollection
	 * @return array
	 *
	 */

	protected function readFieldnamesByContent($module, FieldModuleCollection $pFieldsCollection = null): array
	{
		$pFieldnames = new Fieldnames($pFieldsCollection ?? new FieldsCollection());
		$pFieldnames->loadLanguage();

		$fieldnames = $pFieldnames->getFieldList($module);
		$resultByContent = array();
		$categories = array();
		$listTypeUnSupported = ['user', 'datei', 'redhint', 'blackhint', 'dividingline'];
		foreach ($fieldnames as $key => $properties) {
			if (in_array($properties['type'], $listTypeUnSupported)) {
				continue;
			}
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


	/**
	 * @return array
	 */

	public function transformPostValues(): array
	{
		$result = [];

		foreach ( $_POST as $index => $fields ) {
			if ( str_contains( $index, self::EXCLUDE_FIELD ) || str_contains( $index, 'filter_fields_order' ) ) {
				continue;
			}
			if ( is_array( $fields ) ) {
				foreach ( $fields as $key => $field ) {
					if ( $key === 'dummy_key' || $field === 'dummy_key' ) {
						unset( $fields[ $key ] );
						continue;
					}
					if ( is_array( $field ) && ( $index === 'defaultvalue-lang' || $index === 'customlabel-lang' || $index === 'oopluginfieldconfigformdefaultsvalues-value' ) ) {
						$fields[ $key ] = (object) $field;
					}
				}
			}
			if ( $index === 'defaultvalue-lang' || $index === 'customlabel-lang' || $index === 'oopluginfieldconfigformdefaultsvalues-value' ) {
				$result[ $index ] = (object) $fields;
			} else {
				$result[ $index ] = $fields;
			}
		}

		return $result;
	}
}
