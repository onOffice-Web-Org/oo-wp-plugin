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

declare (strict_types=1);

namespace onOffice\WPlugin\Renderer;

use function esc_html;

class SortableTagsRenderer
	extends InputFieldRenderer
{

	/**
	 *
	 * @param string $name
	 * @param string $value
	 *
	 */

	public function __construct($name, $value)
	{
		parent::__construct('sortabletags', $name, $value);
	}

	/**
	 *
	 */
	public function render()
	{
		echo '<div class="wp-clearfix">';
		echo '<label>' . esc_html($this->getLabel()) . '</label>';
		echo '<input type="hidden" name="' . esc_html($this->getName()) . '" class="hidden-sortable-tags" value="' . esc_attr(implode(",", array_keys($this->getValue()))) . '">';
		echo '<ul class="sortable-tags">';
		foreach ($this->getValue() as $key => $name) {
			echo '<li class="ui-state-default sortable-tag" data-key="' . esc_attr($key) . '">';
			echo '<label>' . esc_html($name) . '</label>';
			echo '<span class="dashicons dashicons-ellipsis rotate-icon"></span>';
			echo '</li>';
		}
		echo '</ul>';
		echo '</div>';
	}
}
