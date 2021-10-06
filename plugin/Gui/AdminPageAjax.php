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
use onOffice\WPlugin\API\APIClientUserRightsException;
use onOffice\WPlugin\API\ApiClientException;
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
			try {
				$this->buildForms();
			} catch (APIClientCredentialsException $pCredentialsException) {
				$label = __('login credentials', 'onoffice-for-wp-websites');
				$loginCredentialsLink = sprintf('<a href="admin.php?page=onoffice-settings">%s</a>', $label);
				/* translators: %s will be replaced with the link to the login credentials page. */
				wp_die(sprintf(__('It looks like you did not enter any valid API credentials. '
					.'Please go back and review your %s.', 'onoffice-for-wp-websites'), $loginCredentialsLink), 'onOffice plugin');
			} catch (APIClientUserRightsException $pUserRightsException) {
				$class = 'notice notice-error';
				$message_01 = sprintf(esc_html(__("The onOffice plugin received an error from onOffice enterprise.","onoffice-for-wp-websites")));
				$message_02 = sprintf(esc_html(__("Please open onOffice enterprise and go the API user's \"Rights\" settings. Ensure that the option \"Queries only possible in user context / User ID must be passed with each request\" is not activated as it can only be active for onOffice. Marketplace products and not for this plugin.","onoffice-for-wp-websites")));
				$message_03 = sprintf(esc_html(__("If deactivating that option does not solve the error, please contact web-support@onoffice.de.","onoffice-for-wp-websites")));

				printf('<div class="%1$s"><p>%2$s</p><p>%3$s</p><p>%4$s</p></div>', esc_attr($class), esc_html($message_01), esc_html($message_02), esc_html($message_03));
				wp_die();
			}
			catch (ApiClientException $pApiClientException) {
				$class = 'notice notice-error';
				$errorCode = $pApiClientException->getApiClientAction()->getErrorCode();
				$statusMessage = $pApiClientException->getApiClientAction()->getStatusMessage();
				$message_01 = sprintf(esc_html(__("The onOffice plugin encountered an error. Please help us fix it by contacting web-support@onoffice.de and sending the following information:","onoffice-for-wp-websites")));
				$message_02 = sprintf(esc_html(__("> Technical information:","onoffice-for-wp-websites")));
				/* translators: %s will be replaced with error code from API response */
				$message_03 = sprintf(esc_html(__("Error code: %s","onoffice-for-wp-websites")), $errorCode);
				/* translators: %s will be replaced with status message from API response */
				$message_04 = sprintf(esc_html(__("Status message: %s","onoffice-for-wp-websites")), $statusMessage);

				printf('<details class="%1$s"><summary>%2$s</summary><p>%3$s</p><p>%4$s</p><p>%5$s</p></details>', esc_attr($class), esc_html($message_01), esc_html($message_02), esc_html($message_03), esc_html($message_04));
				wp_die();
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
