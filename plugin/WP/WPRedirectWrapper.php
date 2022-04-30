<?php

declare ( strict_types=1 );

namespace onOffice\WPlugin\WP;

class WPRedirectWrapper
{
	public function redirect( string $url ) {
		wp_redirect( $url, 301 );
		ob_end_flush();
		exit();
	}
}
