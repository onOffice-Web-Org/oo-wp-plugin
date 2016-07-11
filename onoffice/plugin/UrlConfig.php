<?php

/**
 *
 * @author Jakob Jungmann <j.jungmann@onoffice.de>
 * @url http://www.onoffice.de
 * @copyright 2003-2016, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

/**
 *
 */

class UrlConfig {

	/**
	 *
	 * You can either put the configuration for the view into the config of the list
	 * or put in a reference
	 *
	 * @param string $view
	 * @return int the page id
	 *
	 */

	public static function getViewPageIdByConfig( $viewConfig ) {
		$pageid = null;

		if ( is_string( $viewConfig ) ) {
			$substr = substr($viewConfig, 1);
			list($configName, $view) = explode( ':', $substr );
			$estateConfig = ConfigWrapper::getInstance()->getConfigByKey( 'estate' );
			$pageId = $estateConfig[$configName]['views'][$view]['pageid'];
		} elseif ( is_array( $viewConfig ) ) {
			$pageId = $viewConfig['pageid'];
		}

		return $pageId;
	}
}
