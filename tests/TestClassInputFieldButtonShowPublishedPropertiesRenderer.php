<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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

namespace onOffice\tests;

use onOffice\WPlugin\Renderer\InputFieldButtonShowPublishedPropertiesRenderer;

class TestClassInputFieldButtonShowPublishedPropertiesRenderer
	extends \WP_UnitTestCase
{
	public function testAttributes()
	{
		$pRenderer = new InputFieldButtonShowPublishedPropertiesRenderer('button', '');
		$pRenderer->setLabel('testLabel');
		$this->assertEquals('testLabel', $pRenderer->getLabel());
	}

	/**
	 *
	 */
	public function testRenderWithLabel()
	{
		$pRenderer = new InputFieldButtonShowPublishedPropertiesRenderer('button', '');
		$pRenderer->setLabel('Show published properties');
		ob_start();
		$pRenderer->render();
		$output = ob_get_clean();
		$this->assertEquals('<p class="wp-clearfix custom-input-field"><span><span class="spinner"></span></span><button id="show-published-properties" class="button action">Show published properties</button><div class="message-show-published-properties" style="display:none"></div></p>', $output);
	}
}