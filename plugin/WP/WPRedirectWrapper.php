<?php
declare (strict_types=1);

namespace onOffice\WPlugin\WP;

class WPRedirectWrapper
{
	public function redirect(string $url)
	{
		return wp_redirect($url, 301);
	}
}
