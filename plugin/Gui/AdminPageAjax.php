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
use onOffice\WPlugin\API\APIEmptyResultException;
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
			} catch ( APIEmptyResultException $pEmptyResultException ) {
				$label = __('The onOffice plugin has an unexpected problem when trying to reach the onOffice API.', 'onoffice-for-wp-websites');
				$labelOnOfficeServerStatus = __( 'onOffice server status', 'onoffice-for-wp-websites' );
				$onOfficeServerStatusLink  = sprintf( '<a href="https://status.onoffice.de/">%s</a>', $labelOnOfficeServerStatus );
				$labelSupportFormLink      = __( 'support form', 'onoffice-for-wp-websites' );
				$supportFormLink           = sprintf( '<a href="https://wp-plugin.onoffice.com/en/support/">%s</a>', $labelSupportFormLink );
				/* translators: %1$s is office server status page link, %2$s is support form page link */
				$message                   = sprintf( esc_html( __( 'Please check the %1$s to see if there are known problems. Otherwise, report the problem using the %2$s.',
					'onoffice-for-wp-websites' ) ), $onOfficeServerStatusLink, $supportFormLink );
				wp_die( sprintf( '<div><p>%1$s</p><p>%2$s</p></div>', $label, $message ) );
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

		// WordPress escapes quotes (and backslashes) in $_POST. This is called "magic quotes", for details see https://core.trac.wordpress.org/ticket/18322.
		// If we would save the strings with the backslashes, those would not be unescaped correctly later, so on the next save, we would keep adding backslashes.
		// Therefore, we unescape all strings here.
		$normalizedPost = wp_unslash($_POST);

		foreach ( $normalizedPost as $index => $fields ) {
			if ( strpos( $index, self::EXCLUDE_FIELD ) !== false || strpos( $index, 'filter_fields_order' ) !== false ) {
				continue;
			}
			if ( is_array( $fields ) ) {
				foreach ( $fields as $key => $field ) {
					if ( $key === 'dummy_key' || $field === 'dummy_key' ) {
						unset( $fields[ $key ] );
						continue;
					}
					if ( is_array( $field ) && ( $index === 'defaultvalue-lang' || $index === 'customlabel-lang' || $index === 'oopluginfieldconfigformdefaultsvalues-value' || $index === 'oopluginfieldconfigestatedefaultsvalues-value') ) {
						$fields[ $key ] = (object) $field;
					}
				}
			}
			if ( $index === 'defaultvalue-lang' || $index === 'customlabel-lang' || $index === 'oopluginfieldconfigformdefaultsvalues-value' || $index === 'oopluginfieldconfigestatedefaultsvalues-value') {
				$result[ $index ] = (object) $fields;
			} else {
				$result[ $index ] = $fields;
			}
		}

		return $result;
	}
}
