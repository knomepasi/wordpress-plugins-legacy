<?php
/*
 *  Plugin Name: Body Classes for Network
 *  Description: Add network-related body classes.
 *  Author: Pasi Lallinaho
 *  Version: 1.0.1
 *  Author URI: http://open.knome.fi/
 *  Plugin URI: http://wordpress.knome.fi/
 *
 *  License: GNU General Public License v2 or later
 *  License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

add_filter( 'body_class', 'body_classes_network' );

function body_classes_network( $classes ) {
	$classes[] = 'blogid-' . get_current_blog_id( );

	$this_url = parse_url( get_bloginfo( 'url' ) );
	$classes[] = 'host-' . str_replace( '.', '-', $this_url['host'] );

	if( get_current_blog_id( ) == 1 && is_multisite( ) ) {
		$classes[] = 'is-network-front';
		$classes[] = 'is-multisite-front';
	}

	return $classes;
}

?>
