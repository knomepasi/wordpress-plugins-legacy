<?php
/*
 *  Plugin Name: Block Guest Access
 *  Description: Restrict access to the site to users only.
 *  Author: Pasi Lallinaho
 *  Version: 0.2
 *  Author URI: http://open.knome.fi/
 *  Plugin URI: http://wordpress.knome.fi/
 *
 *  License: GNU General Public License v2 or later
 *  License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

/*
 *  TODO:
 *  – Allow redirecting to either the login page or a blank page
 *
 */

add_action( 'template_redirect', 'block_guest_access_check' );

function block_guest_access_check( ) {
	if( !is_user_logged_in( ) ) {
		wp_redirect( wp_login_url( get_permalink( ) ) );
	}
}

?>