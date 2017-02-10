<?php
/*
 *  Plugin Name: Simple Social Likes
 *  Description: Shows Like/+1 buttons for Facebook/Google+.
 *  Author: Pasi Lallinaho
 *  Version: 1.0.1
 *  Author URI: http://open.knome.fi/
 *  Plugin URI: http://wordpress.knome.fi/
 *
 *  License: GNU General Public License v2 or later
 *  License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

/*  Init plugin
 *
 */

add_action( 'plugins_loaded', 'SimpleSocialLikesInit' );

function SimpleSocialLikesInit( ) {
	/* Load text domain for i18n */
	load_plugin_textdomain( 'simple-social-likes', false, dirname( plugin_basename( FILE ) ) . '/languages/' );
}

/*  Include scripts and default stylesheets
 *
 */

add_action( 'wp_enqueue_scripts', 'SimpleSocialLikesScripts' );

function SimpleSocialLikesScripts( ) {
	wp_register_style( 'simplesociallikes-defaults', plugins_url( 'defaults.css', __FILE__ ) );
	wp_enqueue_style( 'simplesociallikes-defaults' );
}

/*  Include functions for different platforms
 *
 */

include 'facebook.php';
include 'googleplus.php';

/*  Helper function to print the like buttons
 *
 */

function _SimpleSocialLikes_IFrame( $site, $id ) {
	switch( $site ) {
		case 'fb':
			return '<iframe class="simplefb" src="https://www.facebook.com/plugins/like.php?href=http%3A%2F%2Ffacebook.com%2F' . $id . '&width=292&height=62&colorscheme=light&show_faces=false&border_color&stream=false&header=false" width="292" height="24"></iframe>';
			break;
		case 'google':
			add_action( 'wp_footer', '_SimpleSocialLikesGoogleScript' );
			if( $id ) {
				return '<div class="g-plusone" data-size="medium" data-href="' . $id . '"></div>';
			} else {
				return '<div class="g-plusone" data-size="medium"></div>';
			}
			break;
	}
}

function _SimpleSocialLikesGoogleScript( ) {
	echo '<script src="https://apis.google.com/js/platform.js" async defer></script>';
}

?>
