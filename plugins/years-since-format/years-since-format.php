<?php
/*
 *  Plugin Name: Years Since... (with formatting)
 *  Description: A shortcode to retrieve the number of years since a specified date with formatting.
 *  Author: Pasi Lallinaho
 *  Version: 0.1
 *  Author URI: https://open.knome.fi/
 *  Plugin URI: https://wordpress.knome.fi/
 *
 *  License: GNU General Public License v2 or later
 *  License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

add_shortcode( 'ysf', 'YearsSinceFormatShortcode' );

function YearsSinceFormatShortcode( $atts, $content, $code ) {
	$opts = shortcode_atts( array(
		'date' => null,
		'format' => null
	), $atts );

	list( $then_year, $then_month, $then_day ) = explode( '-', $opts['date'] );

	$now_year = date( 'Y' );
	$now_month = date( 'n' );
	$now_day = date( 'j' );

	if( $now_year > $then_year ) {
		// Current year is larger
		if( $now_month > $then_month ) {
			// Current month is larger
			$diff = $now_year - $then_year;
		} elseif( $now_month == $then_month ) {
			// Current month is same
			if( $now_day >= $then_day ) {
				$diff = $now_year - $then_year;
			} else {
				$diff = $now_year - $then_year - 1;
			}
		} else {
			$diff = $now_year - $then_year - 1;
		}
	} else {
		// Same year, return 0
		$diff = 0;
	}

	if( $opts['format'] ) {
		if( strpos( $opts['format'], '%d' ) !== false ) {
			return sprintf( $opts['format'], $diff );
		} else {
			return null;
		}
	} else {
		return $diff;
	}
}

?>
