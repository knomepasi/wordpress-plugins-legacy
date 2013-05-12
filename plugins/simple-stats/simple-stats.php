<?php
/*
 *  Plugin Name: Simple Stats
 *  Description: Simple hit, visitor and referrer statistics.
 *  Author: Pasi Lallinaho
 *  Version: 2.0-alpha4
 *  Author URI: http://open.knome.fi/
 *  Plugin URI: http://wordpress.knome.fi/
 *
 */

/*  Plugin activation
 *
 */

register_activation_hook( __FILE__, 'SimpleStatsActivate' );

function SimpleStatsActivate( ) {
	/* Add option to use a table per blog */
	add_site_option( 'simplestats_table_per_blog', false );
	add_option( 'simplestats_results_visible_default', 20 );
	add_option( 'simplestats_bot_user_agents', '' );

	global $wpdb;
	if( get_site_option( 'simplestats_table_per_blog' ) == true ) {
		$wpdb->simplestats = $wpdb->prefix . "simplestats";
	} else {
		$wpdb->simplestats = $wpdb->base_prefix . "simplestats";
	}

	/* Create database if needed */
	$sql = "CREATE TABLE IF NOT EXISTS $wpdb->simplestats (
		blog_id bigint(20) UNSIGNED NOT NULL,
		context varchar(20) NOT NULL,
		item varchar(255) NOT NULL,
		month date NOT NULL,
		count bigint(20) UNSIGNED NOT NULL,
		UNIQUE KEY id (blog_id,context,item,month)
	);";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	/* Add capabilities */
	$admin = get_role( 'administrator' );
	$admin->add_cap( 'see_stats' );
}

/*  Init plugin
 *
 */

add_action( 'plugins_loaded', 'SimpleStatsInit' );

function SimpleStatsInit( ) {
	/* Load text domain for i18n */
	load_plugin_textdomain( 'simple-stats', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	/* Init database */
	global $wpdb;
	if( get_site_option( 'simplestats_table_per_blog' ) == true ) {
		$wpdb->simplestats = $wpdb->prefix . "simplestats";
	} else {
		$wpdb->simplestats = $wpdb->base_prefix . "simplestats";
	}
}

/*  Include scripts and stylesheets
 *
 */

add_action( 'admin_enqueue_scripts', 'SimpleStatsScripts' );

function SimpleStatsScripts( ) {
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-blind' );

	wp_register_script( 'simple-stats-admin', plugins_url( 'admin.js', __FILE__ ), array( 'jquery' ), '0.1' );
	wp_enqueue_script( 'simple-stats-admin' );

	wp_register_style( 'simple-stats-admin', plugins_url( 'admin.css', __FILE__ ) );
	wp_enqueue_style( 'simple-stats-admin' );
}

/*  Add hook to all page loads
 *
 */

add_action( 'shutdown', 'SimpleStatsHit' );

function SimpleStatsHit( ) {
	global $blog_id, $wpdb; $wpdb->show_errors();

	$month = gmdate( "Y-m-00" );
	$post_id = get_the_ID( );
	$visitor_ip = $_SERVER['REMOTE_ADDR'];
	$referrer = $_SERVER['HTTP_REFERER'];
	$user_agent = strtolower( $_SERVER['HTTP_USER_AGENT'] );

	// Bot handling
	$bot_agents = array(
		'bot', 'crawler', 'robot', 'spider', // Generic crawlers
		'aggregator', 'feedbooster', 'feedfetcher', 'feedparser', 'syndication', // Generic feed aggregators
	);

	$bots = array_merge( $bot_agents, explode( ' ', get_option( 'simplestats_bot_user_agents' ) ) );

	foreach( $bots as $bot ) {
		if( strlen( $bot ) > 0 ) {
			if( strpos( $user_agent, $bot ) !== false ) {
				// It's a bot, ignore
				exit;
			}
		}
	}

	$rows = $wpdb->get_results( $wpdb->prepare( "
		SELECT context, item, count FROM $wpdb->simplestats
		WHERE blog_id = %d AND month = %s AND (
			( context = 'hit' AND item = %s ) OR
			( context = 'referrer' AND item = %s ) OR
			( context = 'visitor' AND item = %s )
		)
		", $blog_id, $month, $post_id, $referrer, $visitor_ip ), ARRAY_A );

	foreach( $rows as $row ) {
		$contexts[$row['context']] = $row;
	}

	/* Referrers */
	/* Make sure we don't get stupid results */
	if( strlen( $referrer ) > 0 && strpos( $referrer, get_option( 'home' ) ) === false && strpos( $referrer, 'wp-admin' ) === false ) {
		if( $contexts['referrer']['count'] < 1 ) {
			$wpdb->insert(
				$wpdb->simplestats,
				array( 'blog_id' => $blog_id, 'context' => 'referrer', 'item' => $referrer, 'month' => $month, 'count' => 1 )
			);
		} else {
			$wpdb->update(
				$wpdb->simplestats,
				array( 'count' => $contexts['referrer']['count'] + 1 ),
				array( 'blog_id' => $blog_id, 'context' => 'referrer', 'item' => $referrer, 'month' => $month ),
				array( '%d' ),
				array( '%d', '%s', '%s', '%s' )
			);
		}
	}

	/* Unique visitors */
	if( $contexts['visitor']['count'] < 1 ) {
		$wpdb->insert(
			$wpdb->simplestats,
			array( 'blog_id' => $blog_id, 'context' => 'visitor', 'item' => $visitor_ip, 'month' => $month, 'count' => 1 )
		);
	}

	/* Hits */
	/* Don't update the stats if we aren't on a single page... */
	// FIXME: Handle frontpage, archives, ...
	if( $post_id != 0 && is_single( ) ) {
		if( $contexts['hit']['count'] < 1 ) {
			$wpdb->insert(
				$wpdb->simplestats,
				array( 'blog_id' => $blog_id, 'context' => 'hit', 'item' => $post_id, 'month' => $month, 'count' => 1 )
			);
		} else {
			$wpdb->update(
				$wpdb->simplestats,
				array( 'count' => $contexts['hit']['count'] + 1 ),
				array( 'blog_id' => $blog_id, 'context' => 'hit', 'item' => $post_id, 'month' => $month ),
				array( '%d' ),
				array( '%d', '%s', '%d', '%s' )
			);
		}
	}
}

/*  Add stats to admin interface
 *
 */

add_action( 'admin_menu', 'SimpleStatsMenu' );

function SimpleStatsMenu( ) {
	if( current_user_can( 'see_stats' ) ) {
		add_menu_page( __( 'Statistics', 'simple-stats' ), __( 'Statistics', 'simple-stats' ), 'see_stats', 'simple-stats', 'SimpleStatsAdminOverview', null, 100 );
		add_submenu_page( 'simple-stats', __( 'Monthly overview', 'simple-stats' ), __( 'Overview', 'simple-stats' ), 'see_stats', 'simple-stats', 'SimpleStatsAdminOverview' );
		add_submenu_page( 'simple-stats', __( 'Popularity', 'simple-stats' ), __( 'Popular articles', 'simple-stats' ), 'see_stats', 'simple-stats-hits', 'SimpleStatsAdminHits' );
		add_submenu_page( 'simple-stats', __( 'Referrers', 'simple-stats' ), __( 'Referrers', 'simple-stats' ), 'see_stats', 'simple-stats-referrers', 'SimpleStatsAdminReferrers' );
	}

	// FIXME: Add custom icon
	// FIXME: Add network wide stats
}

function SimpleStatsAdminOverview( ) {
	if( current_user_can( 'see_stats' ) ) {
		print '<div class="wrap">';
			print '<div id="icon-edit" class="icon32 icon32-simple-stats"><br /></div>';
			print '<h2>' . __( 'Monthly overview', 'simple-stats' ) . '</h2>';

			_simple_stats_overview_table( );			
		print '</div>';
	}
}

function SimpleStatsAdminHits( ) {
	if( current_user_can( 'see_stats' ) ) {
		print '<div class="wrap">';
			print '<div id="icon-edit" class="icon32 icon32-simple-stats"><br /></div>';
			print '<h2>' . __( 'Popularity', 'simple-stats' ) . _simple_stats_title_postfix( ) . '</h2>';

			_simple_stats_months_dropdown( 'simple-stats-hits' );
			_simple_stats_items_list( 'hit', $_POST['month'], $_POST['year'], 'simple-stats-hits' );
		print '</div>';
	}
}

function SimpleStatsAdminReferrers( ) {
	if( current_user_can( 'see_stats' ) ) {
		print '<div class="wrap">';
			print '<div id="icon-edit" class="icon32 icon32-simple-stats"><br /></div>';
			print '<h2>' . __( 'Referrers', 'simple-stats' ) . _simple_stats_title_postfix( ) . '</h2>';

			_simple_stats_months_dropdown( 'simple-stats-referrers' );
			_simple_stats_items_list( 'referrer', $_POST['month'], $_POST['year'], 'simple-stats-referrers' );
		print '</div>';
	}
}

/*  Helper functions
 *
 */

function _simple_stats_items_list( $context, $month = null, $year = null, $redirect_to = null ) {
	global $blog_id, $wpdb;

	if( $year ) {
		$filter = $wpdb->prepare( " AND YEAR( month ) = '%d'", $year );
	} elseif( $month ) {
		$filter = $wpdb->prepare( " AND month = %s", $month );
	} elseif( $_POST['submit-forever'] ) {
		unset( $filter );
	} else {
		$filter = $wpdb->prepare( " AND month = %s", gmdate( "Y-m-00" ) );
	}

	$opts['hit'] = array(
		'amount_text' => __( 'Hits', 'simple-stats' ),
		'item_text' => __( 'Post', 'simple-stats' ),
		'noresults_text' => __( 'No hits during this period of time.', 'simple-stats' )
	);
	$opts['referrer'] = array(
		'amount_text' => __( 'References', 'simple-stats' ),
		'item_text' => __( 'URL', 'simple-stats' ),
		'noresults_text' => __( 'No referrers during this period of time.', 'simple-stats' )
	);

	/* Print results */
	$totals = $wpdb->get_results( $wpdb->prepare( "SELECT item, SUM( count ) as total FROM $wpdb->simplestats WHERE blog_id = %d AND context = %s $filter GROUP BY item ORDER BY total DESC", $blog_id, $context ), ARRAY_A );
	print '<table class="widefat"><thead><tr><th style="width: 80px;">' . $opts[$context]['amount_text'] . '</th><th>' . $opts[$context]['item_text'] . '</th></tr></thead><tbody>';
	if( count( $totals ) > 0 ) {
		foreach( $totals as $item ) {
			if( $items_count == get_option( 'simplestats_results_visible_default' ) && $_GET['show'] != "all" ) {
				print '</tbody><tbody class="more" style="display: none;">';
			}

			if( $context == "hit" ) {
				$cur_post = get_post( $item['item'] );
				print '<tr><td>' . $item['total'] . '</td><td><a href="' . get_permalink( $cur_post ) . '">' . $cur_post->post_title . '</a></td></tr>';
			} elseif( $context == "referrer" ) {
				print '<tr><td>' . $item['total'] . '</td><td><a href="' . $item['item'] . '">' . $item['item'] . '</a></td></tr>';
			}
			$items_count++;
		}
	} else {
		print '<tr><td colspan="2">' . $opts[$context]['noresults_text'] . '</td></tr>';
	}
	print '</tbody></table>';

	if( $items_count > get_option( 'simplestats_results_visible_default' ) && $_GET['show'] != "all" ) {
		print '<p class="show-more"><a href="admin.php?page=' . $redirect_to . '&show=all">' . __( 'Show more results', 'simple-stats' ) . '</a></p>';
	}
}

function _simple_stats_months_dropdown( $redirect_to = 'simple-stats' ) {
	global $blog_id, $wpdb;

	if( $_POST['submit-forever'] ) {
		unset( $_POST['month'], $_POST['year'] );
	} else {
		if( !$_POST['submit-month'] ) { unset( $_POST['month'] ); }
		if( !$_POST['submit-year'] ) { unset( $_POST['year'] ); }
		if( !$_POST['submit-month'] && !$_POST['submit-year'] ) { $_POST['month'] = gmdate( "Y-m-00" ); }
	}

	$where = $wpdb->prepare( "WHERE blog_id = %d", $blog_id );
	$months = $wpdb->get_results( "SELECT DATE_FORMAT( month, '%m' ) as m_month, DATE_FORMAT( month, '%Y' ) as m_year, month FROM {$wpdb->simplestats} {$where} GROUP BY month ORDER BY month DESC", ARRAY_A );
	if( is_array( $months ) ) {
		$select_m = '<select name="month" class="postform">';
		$select_m .= '<option value="" ' . selected( $_POST['month'], false, false ) . '>' . __( '-- Month --', 'simple-stats' ) . '</option>';

		$select_y = '<select name="year" class="postform">';
		$select_y .= '<option value="" ' . selected( $_POST['year'], false, false ) . '>' . __( '-- Year --', 'simple-stats' ) . '</option>';

		foreach( $months as $month ) {
			if( $last_year != $month['m_year'] ) {
				$select_y .= '<option value="' . $month['m_year'] . '" ' . selected( $_POST['year'], $month['m_year'], false ) . ' >' . $month['m_year'] . '</option>';
			}			
			$last_year = $month['m_year'];
			$select_m .= '<option value="' . $month['month'] . '" ' . selected( $_POST['month'], $month['month'], false ) . '>' . strftime( "%B", mktime( 0, 0, 0, $month['m_month'] ) ) . ' ' . $month['m_year'] . '</option>';
		}
		$select_y .= '</select> ';
		$select_m .= '</select> ';

		print '<form action="admin.php?page=' . $redirect_to . '" id="statistics-filter" method="post">';
		print '<div class="tablenav actions">';

		print $select_m;
		print '<input type="submit" name="submit-month" class="button-secondary" value="' . __( 'Filter by month', 'simple-stats' ) . '" />';

		print $select_y;
		print '<input type="submit" name="submit-year" class="button-secondary" value="' . __( 'Filter by year', 'simple-stats' ) . '" />';

		print '<input type="submit" name="submit-forever" class="button-primary" value="' . __( 'Show all', 'simple-stats' ) . '" />';

		print '</div>';
		print '</form>';
	}
}

function _simple_stats_overview_table( ) {
	global $blog_id, $wpdb;

	/* Unique visitors */
	$unique = $wpdb->get_results( $wpdb->prepare( "SELECT month, COUNT(*) as total FROM $wpdb->simplestats WHERE blog_id = %d AND context = 'visitor' ORDER BY month DESC", $blog_id ), ARRAY_A );
	foreach( $unique as $row ) {
		$data[$row['month']]['unique'] = $row['total'];
	}

	/* Monthly hits */
	$hits = $wpdb->get_results( $wpdb->prepare( "SELECT month, SUM(count) as total FROM $wpdb->simplestats WHERE blog_id = %d AND ( context = 'hit' OR context = 'pagehit' ) GROUP BY month ORDER BY month DESC", $blog_id ), ARRAY_A );
	foreach( $hits as $row ) {
		$data[$row['month']]['hits'] = $row['total'];
	}

	print '<table class="widefat"><thead><tr>';
	print '<th style="width: 160px;">' . _x( 'Month', 'column header', 'simple-stats' ) . '</th>';
	print '<th>' . _x( 'Hits', 'column header', 'simple-stats' )  . '</th>';
	print '<th>' . _x( 'Unique visitors', 'column header', 'simple-stats' ) . '</th>';
	print '</tr></thead><tbody>';

	if( is_array( $data ) ) {
		foreach( $data as $month => $totals ) {
			print '<tr>';
			print '<td>' . strftime( "%B %Y", mktime( 0, 0, 0, substr( $month, 6, 2 ), 1, substr( $month, 0, 4 ) ) ) . '</td>';
			print '<td>' . $totals['hits'] . '</td>';
			print '<td>' . $totals['unique'] . '</td>';
			print '</tr>';
		}
	} else {
		print '<tr><td colspan="2">' . __( 'No data gathered for this period of time.', 'simple-stats' ) . '</td></tr>';
	}
	print '</tbody></table>';
}

function _simple_stats_title_postfix( ) {
	if( $_POST['submit-month'] && $_POST['month'] ) {
		$postfix = ": " . strftime( "%B %Y", mktime( 0, 0, 0, substr( $_POST['month'], 6, 2 ), 1, substr( $_POST['month'], 0, 4 ) ) );
	} elseif( $_POST['submit-year'] && $_POST['year'] ) {
		$postfix = ": " . $_POST['year'];
	} elseif( $_POST['submit-forever'] ) {
		$postfix = ": " . __( 'Forever', 'simple-stats' );
	} else {
		$postfix = ": " . strftime( "%B %Y", time( ) );
	}

	return $postfix;
}

?>
