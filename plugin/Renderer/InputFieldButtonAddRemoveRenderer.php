<?php

/**
 *
 *    Copyright (C) 2017-2019 onOffice GmbH
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

use onOffice\WPlugin\Utility\HtmlIdGenerator;
use DI\ContainerBuilder;
use Exception;
use onOffice\WPlugin\API\APIClientCredentialsException;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use const ONOFFICE_DI_CONFIG_PATH;
use function esc_html;
/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class InputFieldButtonAddRemoveRenderer
	extends InputFieldRenderer
{


    /**
     *
     * @param string $name
     * @param mixed $value
     *
     */

    public function __construct($name, $value)
    {
        parent::__construct('buttonHandleField', $name, $value);
    }


	/**
	 *
	 */

	public function render()
	{
		$textHtml = !empty($this->getHint()) ? '<p class="hint-text">' . $this->getHint() . '</p>' : "";
		if (is_array($this->getValue())) {
			foreach ($this->getValue() as $key => $label) {
				$inputId = 'label'.$this->getGuiId().'b'.$key;
				$actionFieldName = 'labelButtonHandleField'.'-'.$key;
                $onofficeSelect = is_array($this->getCheckedValues()) && in_array($key, $this->getCheckedValues()) ? 'class="inputFieldButton dashicons dashicons-remove '.$actionFieldName.'" typeField="2"' : 'class="inputFieldButton dashicons dashicons-insert '.$actionFieldName.'" typeField="1"';
                echo '<span name="'.esc_html($this->getName()).'"'
                    .'' .$onofficeSelect
                    .'' .$this->renderAdditionalAttributes()
                    . 'value="'.esc_html($key).'"'
                    . 'data-onoffice-category="'.esc_attr($this->getLabel()).'"'
                    . 'id="'.esc_html($inputId).'">'
                    . '</span>';
                echo '<label style="margin-left:5px" for="'.esc_html($inputId).'">'.esc_html($label).'</label><br>'
					.$textHtml;
			}
		}
	}
}
