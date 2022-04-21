<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

use onOffice\WPlugin\Utility\__String;
use onOffice\WPlugin\WP\WPNonceWrapperDefault;
use WP_UnitTestCase;
use function wp_hash;
use function wp_nonce_tick;
use function wp_set_current_user;

/**
 *
 */

class TestClassWPNonceWrapperDefault
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGetRefererInvalid()
	{
		$pWPNonceWrapperDefault = new WPNonceWrapperDefault();
		$this->assertEmpty($pWPNonceWrapperDefault->getReferer());
	}


	/**
	 *
	 */

	public function testGetReferer()
	{
		// only referers from same host as home_url() are allowed
		$_REQUEST['_wp_http_referer'] = 'http://example.org/test';
		$_SERVER['REQUEST_URI'] = 'http://example.org/requesturi';
		$pWPNonceWrapperDefault = new WPNonceWrapperDefault();
		$this->assertEquals('http://example.org/test', $pWPNonceWrapperDefault->getReferer());
	}


	public function testVerifyInvalid()
	{
		$this->expectException(\onOffice\WPlugin\WP\WPNonceVerificationException::class);
		$pWPNonceWrapperDefault = new WPNonceWrapperDefault();
		$pWPNonceWrapperDefault->verify('abc', 'testaction');
	}


	/**
	 *
	 */

	public function testVerify()
	{
		wp_set_current_user(1);

		$nonce = $this->buildValidNonce();
		$pWPNonceWrapperDefault = new WPNonceWrapperDefault();
		$this->assertEquals(1, $pWPNonceWrapperDefault->verify($nonce, 'testaction'));
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function buildValidNonce(): string
	{
		$token = wp_get_session_token();
		$hash = wp_hash(wp_nonce_tick().'|testaction|1|'.$token, 'nonce');
		return __String::getNew($hash)->sub(-12, 10);
	}


	/**
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 */

	public function testSafeRedirect()
	{
		$pWPNonceWrapperDefault = new WPNonceWrapperDefault();
		$pWPNonceWrapperDefault->safeRedirect('https://example.org/test1');
		$this->assertContains('Location: https://example.org/test1', xdebug_get_headers());
	}


	public function testSafeRedirectFail()
	{
		$this->expectException(\onOffice\WPlugin\WP\WPRedirectException::class);
		add_filter('wp_redirect', function(string $location) {
			if ($location === 'https://example.org/test1') {
				return false;
			}
			return $location;
		}, 10, 1);
		$pWPNonceWrapperDefault = new WPNonceWrapperDefault();
		$pWPNonceWrapperDefault->safeRedirect('https://example.org/test1');
	}
}