<?php

declare ( strict_types=1 );

namespace onOffice\WPlugin\WP;

use onOffice\WPlugin\Utility\UrlHelper;

class WPRedirectWrapper
{
	public function redirect( string $url ) {
		if ( $this->isCurrentRequest( $url ) ) {
			return;
		}

		$this->doRedirect( $url );
	}


	/**
	 * @param string $url
	 *
	 * @return void
	 */

	protected function doRedirect( string $url )
	{
		redirect_canonical( $url );
	}


	/**
	 * Loop protection: never redirect to the location the browser already asked for.
	 *
	 * @param string $url
	 *
	 * @return bool
	 */

	private function isCurrentRequest( string $url ): bool
	{
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		// esc_url_raw() keeps the percent encoded octets sanitize_text_field() would strip.
		$requestUri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );

		return UrlHelper::isSameLocation(
			$this->getPathAndQuery( $url ),
			$this->getPathAndQuery( $requestUri )
		);
	}


	/**
	 * @param string $url
	 *
	 * @return string
	 */

	private function getPathAndQuery( string $url ): string
	{
		$urlElements = wp_parse_url( $url );
		$pathAndQuery = UrlHelper::getPath( $urlElements );

		if ( ! empty( $urlElements['query'] ) ) {
			$pathAndQuery .= '?' . $urlElements['query'];
		}

		return $pathAndQuery;
	}
}
