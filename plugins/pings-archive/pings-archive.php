<?php
/*
 *  Plugin Name: Pings Archive
 *  Description: Show all of your pings on a page with a shortcode.
 *  Author: Pasi Lallinaho
 *  Version: 0.1
 *  Author URI: https://open.knome.fi/
 *  Plugin URI: https://wordpress.knome.fi/
 *
 *  License: GNU General Public License v2 or later
 *  License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 */

/*  TODO:
 *  Allow using codewords for targets:
 *    'current', 'parent', 'children', 'siblings'
 *
 */

add_shortcode( 'pings', 'PingsShortcode' );

function PingsShortcode( $atts, $content, $code ) {
	global $wpdb;

	$opts = shortcode_atts( array(
		'targets' => null,
		'n' => null,
		'd' => null
	), $atts );

	if( $opts['targets'] ) {
		/* Filter out pings for specified targets */
		$targets = explode( ",", $opts['targets'] );
		$tw = " AND ( ";
		foreach( $targets as $target ) {
			$posts[] = 'comment_post_ID="' . $target . '"';
		}
		$tw .= implode( ' OR ', $posts );
		$tw .= " ) ";
		$tw = $wpdb->prepare( $tw );
	}

	if( $opts['n'] ) {
		/* Limit the amount of pings to show */
		$limit = $wpdb->prepare( ' LIMIT ' . (int) $opts['n'] . ' ' );
	}

	if( $opts['d'] ) {
		/* Limit the age of pings by days */
		$date = ' AND comment_date_gmt >= SUBDATE( NOW(), INTERVAL ' . (int) $opts['d'] . ' DAY ) ';
	}

	$pings = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'comments WHERE comment_type="pingback" AND comment_approved="1"' . $date . $tw . 'ORDER BY comment_date_gmt DESC' . $limit, OBJECT );

	$out = '<div class="pings-archive">';
	if( count( $pings ) > 0 ) {
		if( $limit ) { 
			$message[] = sprintf( _n( 'Newest ping', '%s newest pings', $opts['n'], 'pings-archive' ), $opts['n'] );
		}
		if( $date ) {
			if( $opts['n'] > 1 ) {
				$message[] = sprintf( _n( 'today', 'in the last %d days', $opts['d'], 'pings-archive' ), $opts['d'] );
			} elseif( !$limit ) {
				$message[] = sprintf( _n( 'Pings today', 'Pings in the last %d days', $opts['d'], 'pings-archive' ), $opts['d'] );
			}
		}
		if( is_array( $message ) ) {
			$out .= '<p>' . implode( ' ', $message ) . '</p>';
		}

		$out .= '<ul>';
		foreach( $pings as $ping ) {
			$out .= '<li><a href="' . $ping->comment_author_url . '">' . $ping->comment_author . '</a><br />' . $ping->comment_content . '</li>';
		}
		$out .= '</ul>';
	} else {
		$out .= '<p>' . __( 'No pingbacks found.', 'pings-archive' ) . '</p>';
	}
	$out .= '</div>';

	return $out;
}

?>
