<?php
/*
 *  Plugin Name: Dashboard Drafts Plus
 *  Description: Adds a Dashboard widget showing all drafts for all post types.
 *  Author: Pasi Lallinaho
 *  Version: 1.0
 *  Author URI: http://open.knome.fi/
 *  Plugin URI: http://wordpress.knome.fi/
 *
 *  License: GNU General Public License v2 or later
 *  License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

/*
 *  Load text domain for translations
 *
 */

add_action( 'plugins_loaded', 'dashboard_drafts_plus_init' );

function dashboard_drafts_plus_init( ) {
	load_plugin_textdomain( 'dashboard-drafts-plus', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

/*
 *  Include a stylesheet for the admin
 *
 */

add_action( 'admin_enqueue_scripts', 'dashboard_drafts_plus_admin_scripts' );

function dashboard_drafts_plus_admin_scripts( ) {
	wp_enqueue_style( 'dasboard-drafts-plus-admin-style', plugins_url( 'dashboard-drafts-plus-admin.css', __FILE__ ) );
}


/*
 *  Register the widget for the dashboard
 *
 */

add_action( 'wp_dashboard_setup', 'dashboard_drafts_plus_dashboard_setup' );

function dashboard_drafts_plus_dashboard_setup( ) {
	wp_add_dashboard_widget( 'dashboard_drafts_plus', __( 'All Drafts', 'dashboard-drafts-plus' ), 'dashboard_drafts_plus_dashboard_widget' );
}

function dashboard_drafts_plus_dashboard_widget( ) {
	$post_types = get_post_types( array( ), 'objects' );

	foreach( $post_types as $post_type ) {
		$args = array(
			'posts_per_page' => -1,
			'post_type' => $post_type->name,
			'orderby' => 'modified',
			'order' => 'DESC',
			'post_status' => 'draft'
		);
		$drafts = get_posts( $args );

		if( count( $drafts ) > 0 ) {
			echo '<div class="dashboard-drafts-plus">';
			echo '<p class="view-all"><a href="' . admin_url( 'edit.php?post_type=' . $post_type->name . '&post_status=draft' ) . '">' . __( 'View all', 'dashboard-drafts-plus' ) . '</a></p>';
			echo '<h4 class="post-count">' . $post_type->labels->name . '</h4>';
			echo '<ul>';
			foreach( $drafts as $draft ) {
				echo '<li>';
				echo '<span><time datetime="' . get_the_time( 'c', $draft ) . '">' . get_the_time( get_option( 'date_format' ), $draft ) . '</time></span>';
				echo '<a href="' . get_edit_post_link( $draft->ID ) . '">' . $draft->post_title . '</a>';
				echo '</li>';
			}
			echo '</ul>';
			echo '</div>';
		}
	}
}

?>
