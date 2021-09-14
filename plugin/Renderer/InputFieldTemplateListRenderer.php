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
		'onoffice-editlistview' => 'default.php',
		'onoffice-editunitlist' => 'default_units.php',
		'onoffice-estates' =>
			[
				'similar-estates' => 'similar_estates.php',
				'detail' => 'default_detail.php'
			],
		'onoffice-editform' => [
			'contact' => 'defaultform.php',
			'interest' => 'applicantform.php',
			'owner' => 'ownerform.php',
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
		foreach ($this->getValue() as $templateValue) {
			$templateList = $templateValue['path'];
			if (count($this->getValue()) > 1) {
				if (in_array($this->_checkedValue, $templateList) ||
					array_key_exists($this->_checkedValue, $templateList)) {
					echo '<details open>';
				} else {
					echo '<details>';
				}
				echo '<summary>' . esc_html($templateValue['title']) . '</summary>';
			}
			foreach ($templateList as $key => $label) {
				$checked = false;
				if ($label === $this->_checkedValue || $key === $this->_checkedValue) {
					$checked = true;
					$this->setCheckedValue(null);
				}
				$inputId = 'label' . $this->getGuiId() . 'b' . $key;
				echo '<input type="' . esc_html($this->getType()) . '" name="' . esc_html($this->getName())
					. '" value="' . esc_html($key) . '"'
					. ($checked ? ' checked="checked" ' : '')
					. $this->renderAdditionalAttributes()
					. ' id="' . esc_html($inputId) . '">'
					. '<label for="' . esc_html($inputId) . '">' . esc_html($label) . '</label><br>';
			}
			echo "<p>". esc_html("in the folder " . $templateValue['folder']) ."</p>";
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
		$page = $_GET['page'];
		switch ($page) {
			case 'onoffice-estates':
				$tab = $_GET['tab'];
				$this->setCheckedValue(self::TEMPLATE_DEFAULT_LIST[$page][$tab]);
				break;
			case 'onoffice-editform':
				$type = $_GET['type'];
				$id = (int)$_GET['id'];
				if (!empty($id)) {
					$pDataFormConfigFactory = new DataFormConfigurationFactory();
					$pDataFormConfigFactory->setIsAdminInterface(true);
					$pFormConfiguration = $pDataFormConfigFactory->loadByFormId($id);
					$type = $pFormConfiguration->getFormType();
				}
				$this->setCheckedValue(self::TEMPLATE_DEFAULT_LIST[$page][$type]);
				break;
			default:
				$this->setCheckedValue(self::TEMPLATE_DEFAULT_LIST[$page]);
				break;
		}
	}
}
