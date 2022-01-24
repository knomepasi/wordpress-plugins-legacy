<?php
/*
 *  Plugin Name: X-WordPress-Rationale
 *  Description: Adds custom email header X-WordPress-Rationale for WordPress emails.
 *  Author: Pasi Lallinaho
 *  Version: 2.0
 *  Author URI: https://open.knome.fi/
 *  Plugin URI: https://wordpress.knome.fi/
 *
 *  License: GNU General Public License v2 or later
 *  License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 */

class X_WordPress_Rationale {
	function __construct( ) {
		// Hooks that filter headers only
		$hooks_headers = array(
			'comment_notification_headers' => 'comment_notification',
			'comment_moderation_headers' => 'comment_moderation',
			'user_request_action_email_headers' => 'user_request_action',
			'user_request_confirmed_email_headers' => 'user_request_confirmed',
			'user_erasure_fulfillment_email_headers' => 'user_erasure_fulfillment',
			'wp_privacy_personal_data_email_headers' => 'wp_privacy_personal_data'
		);

		// Hooks that filter the array passed on to wp_mail()
		$hooks_email_array = array(
			'new_site_email' => 'new_site',
			'email_change_email' => 'email_change',
			'invited_user_email' => 'invited_user',
			'wp_installed_email' => 'wp_installed',
			'recovery_mode_email' => 'recovery_mode',
			'password_change_email' => 'password_change',
			'auto_core_update_email' => 'auto_core_update',
			'automatic_updates_debug_email' => 'automatic_updates_debug',
			'site_admin_email_change_email' => 'site_admin_email_change',
			'auto_plugin_theme_update_email' => 'auto_plugin_theme_update',
			'wp_new_user_notification_email' => 'wp_new_user_notification',
			'network_admin_email_change_email' => 'network_admin_email_change',
			'wp_new_user_notification_email_admin' => 'wp_new_user_notification_admin',
			'wp_password_change_notification_email' => 'wp_password_change_notification',

		);

		// Add hooks
		foreach( $hooks_email_array as $hook => $rationale ) {
			add_filter( $hook, function( $e ) use( $rationale ) { $e['headers'] .= $this->headers( $rationale ); return $e; } );
		}
		foreach( $hooks_headers as $hook => $rationale ) {
			add_filter( $hook, function( $h ) use( $rationale ) { return $h . $this->headers( $rationale ); } );
		}
	}

	function headers( $rationale ) {
		return "X-WordPress: 1\n" . "X-WordPress-Rationale: " . $rationale . "\n";
	}
}

new X_WordPress_Rationale( );

?>
