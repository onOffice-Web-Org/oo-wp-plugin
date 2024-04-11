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

use onOffice\WPlugin\Renderer\InputFieldGoogleRecaptchaRenderer;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2023, onOffice(R) GmbH
 *
 */

class TestClassInputFieldGoogleRecaptchaRenderer
	extends WP_UnitTestCase
{
	/**
	 *
	 */
	public function testRenderInputSecretKey()
	{
		$pSubject = new InputFieldGoogleRecaptchaRenderer('googleRecaptchaAccount', 'onoffice-settings-captcha-secretkey', '');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<div class="oo-google-recaptcha-key"><input type="password" name="onoffice-settings-captcha-secretkey" value="" id="googleRecaptchaAccount_3" ><button type="button" class="button" data-toggle="0">
				<span class="dashicons dashicons-visibility oo-icon-eye-secret-key" aria-hidden="true"></span> 
				</button></div>', $output);
	}

	/**
	 *
	 */
	public function testRenderInputSiteKey()
	{
		$pSubject = new InputFieldGoogleRecaptchaRenderer('googleRecaptchaAccount', 'onoffice-settings-captcha-sitekey', '');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<div class="oo-google-recaptcha-key"><input type="password" name="onoffice-settings-captcha-sitekey" value="" id="googleRecaptchaAccount_4" ><button type="button" class="button" data-toggle="0">
				<span class="dashicons dashicons-visibility oo-icon-eye-site-key" aria-hidden="true"></span> 
				</button></div>', $output);
	}
}