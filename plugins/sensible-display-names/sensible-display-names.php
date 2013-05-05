<?php
/*
 *  Plugin Name: Sensible Display Names
 *  Description: Sets users' display names to "First Last" on registration.
 *  Author: Pasi Lallinaho
 *  Version: 1.0-RC1
 *  Author URI: http://open.knome.fi/
 *  Plugin URI: http://wordpress.knome.fi/
 *
 */

add_action( 'user_register', 'sensible_names_register' );

function sensible_names_register( $user_id ) {
	$display = $_POST['first_name'] . " " . $_POST['last_name'];

	global $wpdb;
	$wpdb->update(
		$wpdb->users,
		array( "display_name" => $display ),
		array( "ID" => $user_id ),
		array( "%s" ),
		array( "%d" )
	);
}

?>
