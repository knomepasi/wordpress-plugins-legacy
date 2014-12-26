<?php
/*
 *  Plugin Name: Dashboard Drafts
 *  Description: Adds a Dashboard widget showing all drafts for all post types.
 *  Version: 0.1
 *  Author URI: http://open.knome.fi/
 *  Plugin URI: http://wordpress.knome.fi/
 *
 *  License: GNU General Public License v2 or later
 *  License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

/*  Add some admin CSS
 *
 */

add_action( 'admin_enqueue_scripts', 'DashboardDrafts_CSS' );

function DashboardDrafts_CSS( ) {
	wp_enqueue_style( 'dasboard-drafts-css', plugins_url( 'admin.css', __FILE__ ) );
}


/*  Add the dashboard widget
 *
 */

add_action( 'wp_dashboard_setup', 'DashboardDrafts_Setup' );

function DashboardDrafts_Setup( ) {
	wp_add_dashboard_widget( 'dashboard_drafts', 'Drafts', 'DashboardDrafts' );
}

function DashboardDrafts( ) {
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
			echo '<div class="dashboard-drafts">';
			echo '<p class="view-all"><a href="' . admin_url( 'edit.php?post_type=' . $post_type->name . '&post_status=draft' ) . '">' . __( 'View all' ) . '</a></p>';
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
