<?php
/*
 *  Plugin Name: Block Guest Access
 *  Description: Allow access to the site for registered users only.
 *  Author: Pasi Lallinaho
 *  Version: 0.3
 *  Author URI: http://open.knome.fi/
 *  Plugin URI: http://wordpress.knome.fi/
 *
 *  License: GNU General Public License v2 or later
 *  License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

add_option( 'block_guest_access_mode', 'redirect' );

add_action( 'template_redirect', 'block_guest_access_redirect' );

function block_guest_access_redirect( ) {
	if( 'redirect' != get_option( 'block_guest_access_mode' ) ) {
		return;
	}

	if( !is_user_logged_in( ) ) {
		wp_redirect( wp_login_url( get_permalink( ) ) );
		exit;
	}
}

add_filter( 'template_include', 'block_guest_access_template' );

function block_guest_access_template( $template ) {
	if( 'template' != get_option( 'block_guest_access_mode' ) ) {
		return $template;
	}

	if( !is_user_logged_in( ) ) {
		$new_template = locate_template( array( 'guest.php' ) );
		if( !empty( $new_template ) ) {
			return $new_template;
		}
	}

	return $template;
}

?>