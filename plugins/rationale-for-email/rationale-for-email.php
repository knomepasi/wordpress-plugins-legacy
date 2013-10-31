<?php
/*
 *  Plugin Name: Rationale for Email
 *  Description: Adds custom email headers for comment-related emails.
 *  Author: Pasi Lallinaho
 *  Version: 1.0
 *  Author URI: http://open.knome.fi/
 *  Plugin URI: http://wordpress.knome.fi/
 *
 *  License: GNU General Public License v2 or later
 *  License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

function RationaleNotification( $headers ) {
	$headers .= "X-WordPress: 1\n" . "X-WordPress-Rationale: comment-notification\n";
	return $headers;
}

function RationaleModeration( $headers ) {
	$headers .= "X-WordPress: 1\n" . "X-WordPress-Rationale: comment-moderation\n";
	return $headers;
}

add_filter( 'comment_notification_headers', 'RationaleNotification' );
add_filter( 'comment_moderation_headers', 'RationaleModeration' );

// FIXME: User registration
// FIXME: Changed password

?>
