<?php
namespace onOffice\tests;

use onOffice\WPlugin\WP\WPRedirectWrapper;

class RedirectWrapperMocker extends WPRedirectWrapper
{
	public function redirect(string $url)
	{
		return true;
	}
}
