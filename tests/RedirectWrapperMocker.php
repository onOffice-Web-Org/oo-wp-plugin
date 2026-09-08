<?php

declare (strict_types=1);

namespace onOffice\tests;

use onOffice\WPlugin\WP\WPRedirectWrapper;

/**
 * Records the redirects which pass the loop protection of WPRedirectWrapper.
 */
class RedirectWrapperMocker extends WPRedirectWrapper
{
	/** @var string[] */
	private $_redirects = [];

	/**
	 * @param string $url
	 *
	 * @return void
	 */
	protected function doRedirect(string $url)
	{
		$this->_redirects[] = $url;
	}

	/**
	 * @return string[]
	 */
	public function getRedirects(): array
	{
		return $this->_redirects;
	}
}
