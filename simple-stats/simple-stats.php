<?php
/*
 *  Plugin Name: Simple Stats
 *  Description: Gather simple page/post load statistics.
 *  Author: Pasi Lallinaho
 *  Version: 1.0-RC
 *  Author URI: http://open.knome.fi/
 *  Plugin URI: https://github.com/knomepasi/WordPress-plugins
 *
 */

/*  Create database on plugin activation if needed
 *
 */

register_activation_hook( __FILE__, 'MultisiteStatsDBInit' );

function MultisiteStatsDBInit( ) {
	global $wpdb;
	$wpdb->multisitestats = $wpdb->base_prefix . "multisitestats";

	if( !empty( $wpdb->charset ) ) { $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset"; }
	if( !empty( $wpdb->collate ) ) { $charset_collate .= " COLLATE $wpdb->collate"; }

	$wp_query = "CREATE TABLE IF NOT EXISTS `" . $wpdb->multisitestats . "` (
			blog_id bigint(20) unsigned NOT NULL,
			post_id bigint(20) unsigned NOT NULL,
			month date NOT NULL,
			count bigint(20) unsigned NOT NULL
		)" . $charset_collate;

	$wpdb->query( $wp_query );
}

/*  Init plugin
 *
 */

add_action( 'init', 'MultisiteStatsInit' );

function MultisiteStatsInit( ) {
	/* Load text domain for i18n */
	load_plugin_textdomain( 'multisite-stats' );

	global $wpdb;
	$wpdb->multisitestats = $wpdb->base_prefix . "multisitestats";
}

/*  Add hook to all page loads
 *
 */

add_action( 'shutdown', 'MultisiteStatsHit' );

function MultisiteStatsHit( ) {
	global $blog_id, $wpdb;
	$wpdb->multisitestats = $wpdb->base_prefix . "multisitestats";

	$month = gmdate( "Y-m-00" );
	$post_id = get_the_ID( );

	if( $post_id == 0 || !is_single( ) ) { exit; }

	$r = $wpdb->get_row( "SELECT count, month FROM $wpdb->multisitestats WHERE blog_id = '" . $blog_id . "' AND post_id = '" . $post_id . "' AND month = '" . $month . "'", ARRAY_A );
	if( $r['count'] < 1 ) {
		$wpdb->insert(
			$wpdb->multisitestats,
			array( "blog_id" => $blog_id, "post_id" => $post_id, "month" => $month, "count" => 1 )
		);
	} else {
		$new_count = $r['count'] + 1;
		$wpdb->update(
			$wpdb->multisitestats,
			array( "count" => $new_count ),
			array( "blog_id" => $blog_id, "post_id" => $post_id, "month" => $month )
		);
	}
}

/*  Add stats to admin interface
 *
 */

add_action( 'admin_menu', 'MultisiteStatsMenu' );

function MultisiteStatsMenu( ) {
	if( current_user_can( 'manage_sites' ) ) {
		$ms_stats = add_menu_page( __( 'Statistics', 'multisite-stats' ), 'Statistics', 'see_stats', 'multisite-stats', 'MultisiteStatsAdmin', null, 50 );
	}
}

function MultisiteStatsAdmin( ) {
	if( current_user_can( 'manage_sites' ) ) {
		print '<div class="wrap">';
		print '<h2>' . __( 'Statistics', 'multisite-stats' ) . '</h2>';

		if( !$_POST['submit-month'] ) { unset( $_POST['month'] ); }
		if( !$_POST['submit-year'] ) { unset( $_POST['year'] ); }

		_ms_stats_months( );
		_ms_stats_list( $_POST['month'], $_POST['year'] );
		print '</div>';
	}
}

/*  Helper functions
 *
 */

function _ms_stats_list( $month = null, $year = null ) {
	global $blog_id, $wpdb;

	if( $month ) {
		$filter = $wpdb->prepare( " AND month = %s", $month );
	}
	if( $year ) {
		$filter = $wpdb->prepare( " AND YEAR( month ) = '%d'", $year );
	}

	$totals = $wpdb->get_results( "SELECT post_id, SUM( count ) as total FROM $wpdb->multisitestats WHERE blog_id = '" . $blog_id . "'" . $filter .  " GROUP BY post_id ORDER BY total DESC", ARRAY_A );
	if( is_array( $totals ) ) {
		print '<table class="widefat"><thead><tr><th style="width: 80px;">' . __( 'Loads', 'multisite-stats' ) . '</th><th>' . __( 'Post', 'multisite-stats' ) . '</th></tr></thead><tbody>';
		foreach( $totals as $item ) {
			$cur_post = get_post( $item['post_id'] );
			print "<tr><td>" . $item['total'] . "</td><td><a href='" . get_permalink( $cur_post->ID ) . "'>" . $cur_post->post_title . "</a></td></tr>";
		}
		print "</tbody></table>";
	}
}

function _ms_stats_months( ) {
	global $blog_id, $wpdb;

	$months = $wpdb->get_results( "SELECT DATE_FORMAT( month, '%m' ) as m_month, DATE_FORMAT( month, '%Y' ) as m_year, month FROM {$wpdb->multisitestats} WHERE blog_id = '" . $blog_id . "' GROUP BY month ORDER BY month DESC", ARRAY_A );
	if( is_array( $months ) ) {
		$select_y = '<select name="year" class="postform">';
		$select_y .= '<option value="" ' . selected( $_POST['year'], false, false ) . '>' . __( 'Select year', 'multisite-stats' ) . '</option>';

		$select_m = '<select name="month" class="postform">';
		$select_m .= '<option value="" ' . selected( $_POST['month'], false, false ) . '>' . __( 'Select month', 'multisite-stats' ) . '</option>';
		foreach( $months as $month ) {
			if( $last_year != $month['m_year'] ) {
				$select_y .= '<option value="' . $month['m_year'] . '" ' . selected( $_POST['year'], $month['m_year'], false ) . ' >' . $month['m_year'] . '</option>';
			}			
			$last_year = $month['m_year'];

			$select_m .= '<option value="' . $month['month'] . '" ' . selected( $_POST['month'], $month['month'], false ) . '>' . date( "F", mktime( 0, 0, 0, $month['m_month'] ) ) . ' ' . $month['m_year'] . '</option>';
		}
		$select_y .= '</select> ';
		$select_m .= '</select> ';

		print '<form action="admin.php?page=multisite-stats" id="posts-filter" method="post">';
		print '<div class="tablenav actions">';

		print $select_y;
		print '<input type="submit" name="submit-year" id="post-query-submit" class="button-secondary" value="' . __( 'Filter', 'multisite-stats' ) . '" />';

		print $select_m;
		print '<input type="submit" name="submit-month" id="post-query-submit" class="button-secondary" value="' . __( 'Filter', 'multisite-stats' ) . '" />';

		print '</div>';
		print '</form>';
	}
}

?>
