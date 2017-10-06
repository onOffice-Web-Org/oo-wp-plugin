<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


// set up WP environment
require '../../../wp-load.php';

use onOffice\WPlugin\SDKWrapper;

if (!current_user_can('edit_pages') ||
	!array_key_exists('onoffice-cache-nonce', $_REQUEST) ||
	!wp_verify_nonce( $_REQUEST['onoffice-cache-nonce'], 'onoffice-clear-cache' ))
{
	die();
}

$pSdkWrapper = new SDKWrapper();
$cacheInstances = $pSdkWrapper->getCache();

foreach ($cacheInstances as $pCacheInstance)
{
	$pCacheInstance->clearAll();
}

if ( wp_get_referer() )
{
    wp_safe_redirect( add_query_arg( 'cache-refresh', 'success', wp_get_referer() ) );
}
else
{
    wp_safe_redirect( get_home_url() );
}