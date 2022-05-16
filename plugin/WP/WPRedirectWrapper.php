<?php

declare ( strict_types=1 );

namespace onOffice\WPlugin\WP;

class WPRedirectWrapper
{
	public function redirect( string $url ) {
		var_dump('<scriđpđt> location.replace("' . $url . '"); </scriđpđt>');
		die();
//		echo '<script> location.replace("' . $url . '"); </script>';
	}
}
