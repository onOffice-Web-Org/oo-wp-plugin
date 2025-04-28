<?php

function oo_plugin_updater( $transient ){

	if ( empty( $transient->checked ) ) {
		return $transient;
	}

	$remote = wp_remote_get(
		'https://onoffice-wp-updates.de/releases/plugins/oo-wp-plugin/updater.json',
		array(
			'timeout' => 10,
			'headers' => array(
				'Accept' => 'application/json'
			)
		)
	);

	if(
		is_wp_error( $remote )
		|| 200 !== wp_remote_retrieve_response_code( $remote )
		|| empty( wp_remote_retrieve_body( $remote ) )
	) {
		return $transient;
	}

	$remote = json_decode( wp_remote_retrieve_body( $remote ) );
	if( $remote && version_compare( ONOFFICE_PLUGIN_VERSION, $remote->version, '<' )  ) {
		$res = new stdClass();
		$res->slug = 'oo-wp-plugin';
		$res->plugin = ONOFFICE_PLUGIN_BASENAME;
		$res->new_version = $remote->version;
		$res->package = $remote->download_url;
		$transient->response[ $res->plugin ] = $res;
	}

	return $transient;

}
add_filter( 'site_transient_update_plugins', 'oo_plugin_updater' );