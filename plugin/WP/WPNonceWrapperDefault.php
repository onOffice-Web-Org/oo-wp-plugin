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

namespace onOffice\WPlugin\WP;

use function wp_get_referer;
use function wp_verify_nonce;


/**
 *
 */

class WPNonceWrapperDefault
	implements WPNonceWrapper
{
	/**
	 *
	 * @return string
	 *
	 */

	public function getReferer(): string
	{
		$referer = wp_get_referer();

		if ($referer === false) {
			$referer = '';
		}

		return $referer;
	}


	/**
	 *
	 * @param string $nonce
	 * @param string $action
	 * @return int
	 * @throws WPNonceVerificationException
	 *
	 */

	public function verify(string $nonce, string $action): int
	{
		$result = wp_verify_nonce($nonce, $action);

		if ($result === false) {
			throw new WPNonceVerificationException();
		}

		return $result;
	}


	/**
	 *
	 * @param string $url
	 * @param int $status
	 * @throws WPRedirectException
	 *
	 */

	public function safeRedirect(string $url, int $status = 302)
	{
		if (!wp_safe_redirect($url, $status)) {
			throw new WPRedirectException();
		}
	}
}
