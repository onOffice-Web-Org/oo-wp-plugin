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

use onOffice\WPlugin\Controller\UserCapabilities;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationFactory;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */
class InputFieldTemplateListRenderer
	extends InputFieldRenderer
{
	const TEMPLATE_DEFAULT_LIST = [
		'onoffice-editlistviewaddress' => 'default.php',
		'onoffice-addresses'           => 'default_detail.php',
		'onoffice-editlistview'        => 'default.php',
		'onoffice-editunitlist'        => 'default_units.php',
		'onoffice-estates'             => [
			'similar-estates' => 'similar_estates.php',
			'detail'          => 'default_detail.php'
		],
		'onoffice-editform'            => [
			'contact'         => 'defaultform.php',
			'interest'        => 'applicantform.php',
			'owner'           => 'ownerform.php',
			'applicantsearch' => 'applicantsearchform.php',
		]
	];
	/** @var string */
	private $_checkedValue = null;

	/**
	 *
	 * @param string $name
	 * @param string $value
	 *
	 */

	public function __construct($name, $value)
	{
		parent::__construct('radio', $name, $value);
	}

	/**
	 *
	 */

	public function render()
	{
		if (!$this->checkedValueIsSet()) {
			$this->setDefaultCheckedValue();
		}
		echo '<div class="template-list">';

		$templates = [];
		foreach ($this->getValue() as $templateValue) {
			$isTheme = stripos($templateValue['folder'], 'onoffice-theme') !== false;
			if(!$isTheme && !current_user_can(UserCapabilities::OO_PLUGINCAP_MANAGE_PLUGIN_TEMPLATES)){
				continue;
			}
			$templates[] = $templateValue;
		}

		foreach ($templates as $templateValue) {
			$templateList = $templateValue['path'];
			if (count($templates) > 1) {
				if (in_array($this->_checkedValue, $templateList) ||
					array_key_exists($this->_checkedValue, $templateList)) {
					echo '<details open>';
				} else {
					echo '<details>';
				}
				echo '<summary>' . esc_html($templateValue['title']) . '</summary>';
			}

			foreach ($templateList as $key => $label) {

				if(!current_user_can(UserCapabilities::OO_PLUGINCAP_MANAGE_FORM_APPLICANTSEARCH)){
					if ($label === 'applicantsearchform.php') {
						continue;
					}
				}

				if(!current_user_can(UserCapabilities::OO_PLUGINCAP_MANAGE_FORM_NEWSLETTER)){
					if ($label === 'newsletter.php') {
						continue;
					}
				}
		
				if(!current_user_can(UserCapabilities::OO_PLUGINCAP_MANAGE_FORM_OWNER)){
					if ($label === 'ownerform.php') {
						continue;
					}
				}

				if(!current_user_can(UserCapabilities::OO_PLUGINCAP_MANAGE_FORM_OWNER_LEADGENERATOR)){
					if ($label === 'ownerleadgeneratorform.php') {
						continue;
					}
				}

				$checked = false;
				if ($label === $this->_checkedValue || $key === $this->_checkedValue) {
					$checked = true;
					$this->setCheckedValue(null);
				}
				$inputId = 'label' . $this->getGuiId() . 'b' . $key;
				echo '<input type="' . esc_html($this->getType()) . '" name="' . esc_html($this->getName())
                    . '" value="' . esc_html($key) . '"'
                    . ($checked ? ' checked="checked" ' : '')
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderAdditionalAttributes() returns escaped content
                    . $this->renderAdditionalAttributes()
                    . ' id="' . esc_html($inputId) . '">'
                    . '<label for="' . esc_html($inputId) . '">' . esc_html($label) . '</label><br>';
			}
			/* translators: %s: folder path */
			echo '<p class="oo-template-folder-path">'. esc_html(sprintf(__('(in the folder "%s")', 'onoffice-for-wp-websites'), $templateValue['folder'])) ."</p>";
			echo (count($this->getValue()) > 1) ? '</details>' : '';
		}
		echo '</div>';
	}

	public function checkedValueIsSet()
	{
		$isSet = false;
		if (empty($this->getCheckedValue())) {
			return false;
		}
		foreach ($this->getValue() as $templateValue) {
			if (array_key_exists($this->_checkedValue, $templateValue['path'])) {
				$isSet = true;
				break;
			}
		}

		return $isSet;
	}

	/** @return string|null */
	public function getCheckedValue()
	{
		return $this->_checkedValue;
	}

	/** @param $checkedValue */
	public function setCheckedValue($checkedValue)
	{
		$this->_checkedValue = $checkedValue;
	}

	/**
	 *
	 */
	public function setDefaultCheckedValue()
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Admin display logic, no side effects.
        if (!isset($_GET['page'])) {
            return;
        }
        $page = sanitize_key(wp_unslash($_GET['page']));
        switch ($page) {
            case 'onoffice-estates':
                $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : '';
                if (isset(self::TEMPLATE_DEFAULT_LIST[$page][$tab])) {
                    $this->setCheckedValue(self::TEMPLATE_DEFAULT_LIST[$page][$tab]);
                }
                break;
            case 'onoffice-editform':
                $type = isset($_GET['type']) ? sanitize_key(wp_unslash($_GET['type'])) : '';
                if (!empty($_GET['id'])) {
                    $id = absint(wp_unslash($_GET['id']));
                    $pDataFormConfigFactory = new DataFormConfigurationFactory();
                    $pDataFormConfigFactory->setIsAdminInterface(true);
                    $pFormConfiguration = $pDataFormConfigFactory->loadByFormId($id);
                    $type = $pFormConfiguration->getFormType();
                }
                if (isset(self::TEMPLATE_DEFAULT_LIST[$page][$type])) {
                    $this->setCheckedValue(self::TEMPLATE_DEFAULT_LIST[$page][$type]);
                }
                break;
            default:
                if (isset(self::TEMPLATE_DEFAULT_LIST[$page])) {
                    $this->setCheckedValue(self::TEMPLATE_DEFAULT_LIST[$page]);
                }
                break;
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended
    }
}
