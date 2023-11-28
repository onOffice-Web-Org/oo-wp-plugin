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

namespace onOffice\tests;

use onOffice\WPlugin\Renderer\InputFieldToggleSwitchRenderer;
use WP_UnitTestCase;

class TestClassInputFieldToggleSwitchRenderer extends WP_UnitTestCase
{
	/**
	 *
	 */
	public function testRenderToggleOpenGraph()
	{
		$pSubject = new InputFieldToggleSwitchRenderer('checkbox', 'onoffice-settings-opengraph', '');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<label class="oo-toggle-switch"><input type="checkbox"'
			. ' value="" name="onoffice-settings-opengraph"><span class="slider round"></span></label>', $output);
	}

	/**
	 *
	 */
	public function testRenderToggleTwitterCard()
	{
		$pSubject = new InputFieldToggleSwitchRenderer('checkbox', 'onoffice-settings-twittercards', '');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<label class="oo-toggle-switch"><input type="checkbox"'
			. ' value="" name="onoffice-settings-twittercards"><span class="slider round"></span></label>', $output);
	}
}