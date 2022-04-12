<?php

declare ( strict_types=1 );

namespace onOffice\WPlugin\WP;

class WPRedirectWrapper
{
	public function redirect( string $url ) {
		add_action( 'init', function () {
			ob_start();
		} );
		wp_redirect( $url, 301 );
		exit();
	}
}
