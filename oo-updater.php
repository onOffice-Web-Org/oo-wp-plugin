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
		$required_php = ! empty( $remote->requires_php ) ? (string) $remote->requires_php : '8.2';

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