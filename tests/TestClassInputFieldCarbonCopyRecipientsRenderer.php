<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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

use onOffice\WPlugin\Renderer\InputFieldCarbonCopyRecipientsRenderer;
use WP_UnitTestCase;

class TestClassInputFieldCarbonCopyRecipientsRenderer
	extends WP_UnitTestCase
{
	/**
	 *
	 */
	public function testRenderWithoutCarbonCopyRecipients()
	{
		$pSubject = new InputFieldCarbonCopyRecipientsRenderer('carbonCopyRecipients', '');
		ob_start();
		$pSubject->setMultiple(true);
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<select name="carbonCopyRecipients[]" id="select2_2" multiple ="multiple" ></select><div class="onoffice-cc-recipients-error-message">Invalid emails!</div>',
			$output);
	}

	/**
	 *
	 */
	public function testRenderWithCarbonCopyRecipients()
	{
		$pSubject = new InputFieldCarbonCopyRecipientsRenderer('carbonCopyRecipients', '');
		$carbonCopyRecipients = [
			'test1@gmail.com' => 'test1@gmail.com',
			'test2@gmail.com' => 'test2@gmail.com'
		];
		ob_start();
		$pSubject->setMultiple(true);
		$pSubject->setSelectedValue($carbonCopyRecipients);
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<select name="carbonCopyRecipients[]" id="select2_3" multiple ="multiple" ><option value="test1@gmail.com" selected="selected">test1@gmail.com</option><option value="test2@gmail.com" selected="selected">test2@gmail.com</option></select><div class="onoffice-cc-recipients-error-message">Invalid emails!</div>',
			$output);
	}
}