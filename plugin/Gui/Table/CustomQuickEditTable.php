<?php

/**
 *
 *    Copyright (C) 2023 onOffice GmbH
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

namespace onOffice\WPlugin\Gui\Table;

use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Template\TemplateCall;


/**
 *
 */

class CustomQuickEditTable
{
	/**
	 * @var array
	 */
	private $_field = [];

	/** */
	const CLASS_LEFT_STYLE = 'left';

	/**
	* @param array $field
	*/
	public function __construct(array $field) {
		$this->_field = $field;
	}

	/**
	 * @return string
	 */
	public function renderTemplateQuickEdit() {
		$html = '';
		foreach ($this->_field as $fieldName => $fieldConfig) {
			if ($fieldName === 'template') {
				$fieldConfig = $this->getTemplateFieldConfig($fieldConfig);
			}

			switch ($fieldConfig['type']) {
				case InputModelOption::HTML_TYPE_SELECT:
					$html .= $this->renderSelectField($fieldName, $fieldConfig, self::CLASS_LEFT_STYLE);
					break;
				case InputModelOption::HTML_TYPE_TEXT:
					$html .= $this->renderTextField($fieldName, $fieldConfig, self::CLASS_LEFT_STYLE);
					break;
			}
		}

		return $html;
	}

	/**
	 * @param array $fieldConfig
	 * @return array
	 */
	private function getTemplateFieldConfig(array $fieldConfig) {
		$defaultValueForTemplateField = [];
		$templatePaths = $this->readTemplatePaths($fieldConfig['module']);

		foreach ($templatePaths as $template) {
			if (isset($template['path']) && is_array($template['path'])) {
				foreach (array_keys($template['path']) as $key) {
					$defaultValueForTemplateField[$key] = $key;
				}
			}
		}

		$fieldConfig['default'] = $defaultValueForTemplateField;

		return $fieldConfig;
	}

	/**
	 * @param mixed $directory
	 * @param mixed $pattern
	 * @return array
	 */
	private function readTemplatePaths( $directory, $pattern = '*' ) {
		$templatesAll[ TemplateCall::TEMPLATE_FOLDER_INCLUDED ] = glob( plugin_dir_path( ONOFFICE_PLUGIN_DIR
										. '/index.php' ) . 'templates.dist/' . $directory . '/' . $pattern . '.php' );
		$templatesAll[ TemplateCall::TEMPLATE_FOLDER_PLUGIN ]   = glob( plugin_dir_path( ONOFFICE_PLUGIN_DIR )
										. 'onoffice-personalized/templates/' . $directory . '/' . $pattern . '.php' );
		$templatesAll[ TemplateCall::TEMPLATE_FOLDER_THEME ]    = glob( get_stylesheet_directory()
										. '/onoffice-theme/templates/' . $directory . '/' . $pattern . '.php' );

		return ( new TemplateCall() )->formatTemplatesData( array_filter( $templatesAll ), $directory );
	}

	/**
	 * @param string $fieldName
	 * @param array $fieldConfig
	 * @param string $classStyle
	 * @return string
	 */
	private function renderSelectField(string $fieldName, array $fieldConfig, string $classStyle) {
		$html = '<fieldset class="inline-edit-col-'.$classStyle.'">';
		$html .= '<div class="inline-edit-col">';
		$html .= '<label class="alignleft" for="' . esc_attr($fieldName) . '">';
		$html .= '<span class="title">'. esc_html($fieldConfig['name']) .'</span>';
		$html .= '<select name="' . esc_attr($fieldName) . '" id="' . esc_attr($fieldName) . '" value="">';

		if (isset($fieldConfig['default']) && is_array($fieldConfig['default'])) {
			foreach ($fieldConfig['default'] as $value => $label) {
				$html .= '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
			}
		}

		$html .= '</select>';
		$html .= '</label>';
		$html .= '</div>';
		$html .= '</fieldset>';

		return $html;
	}

	/**
	 * @param string $fieldName
	 * @param array $fieldConfig
	 * @param string $classStyle
	 * @return string
	 */
	private function renderTextField(string $fieldName, array $fieldConfig, string $classStyle) {
		$html = '<fieldset class="inline-edit-col-'.$classStyle.'">';
		$html .= '<div class="inline-edit-col">';
		$html .= '<label>';
		$html .= '<span class="title">'. esc_html($fieldConfig['name']) .'</span>';
		$html .= '<span class="input-text-wrap">';
		$html .= '<input type="text" name=' . esc_attr($fieldName) . ' id="' . esc_attr($fieldConfig['name']) . '" value="">';
		$html .= '</span>';
		$html .= '</label>';
		$html .= '</div>';
		$html .= '</fieldset>';

		return $html;
	}
}