<?php

declare ( strict_types=1 );

namespace onOffice\WPlugin\WP;

class WPRedirectWrapper
{
	public function redirect( string $url ) {
		echo '<script> location.replace("' . $url . '"); </script>';
	}
}
