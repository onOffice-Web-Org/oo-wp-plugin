<?php

namespace onOffice\WPlugin\Utility;

class Redirector
{
	/**
	 * @return mixed
	 */

	public function checkUrlIsMatchRule()
	{
		$uri        = $this->getUri();
		$uriToArray = explode( '/', $uri );
		array_pop( $uriToArray );
		$pagePath = implode( '/', array_filter( $uriToArray ) );

		//Check pass rule and has Unique ID
		preg_match( '/^(' . preg_quote( $pagePath,
				'/' ) . ')\/([0-9]+)(-([^$]+)?)?\/?$/', $uri, $matches );

		return $matches;
	}


	/**
	 * @param  array  $newUrlArr
	 * @param  array  $oldUrlArr
	 *
	 * @return bool
	 */

	public function checkNewUrlIsValid( array $newUrlArr, array $oldUrlArr )
	{
		if ( end( $newUrlArr ) !== end( $oldUrlArr ) ) {
			array_pop( $newUrlArr );
			array_pop( $oldUrlArr );

			return empty( array_diff( $newUrlArr, $oldUrlArr ) );
		}

		return false;
	}


	/**
	 * @return mixed
	 */

	public function getUri()
	{
		global $wp;

		return $wp->request;
	}


	/**
	 * @return string
	 */

	public function getCurrentLink(): string
	{
		global $wp;
		return home_url(add_query_arg($_GET, $wp->request));
	}
}
