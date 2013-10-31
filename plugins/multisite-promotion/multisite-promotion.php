<?php
/*
 *  Plugin Name: Multisite Promotion
 *  Description: Enables adding widgets to link to other (multi)sites for promotion
 *  Author: Pasi Lallinaho
 *  Version: 1.1
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

add_action( 'plugins_loaded', 'MultisitePromotionInit' );

function MultisitePromotionInit( ) {
	/* Load text domain for i18n */
	load_plugin_textdomain( 'multisite-promotion', false, dirname( plugin_basename( FILE ) ) . '/languages/' );
}

/*  Widget
 *
 */

add_action( 'widgets_init', function( ) { register_widget( 'MultisitePromotionWidget' ); } );

class MultisitePromotionWidget extends WP_Widget {
	/** constructor */
	function __construct() {
		$widget_ops = array( 'description' => __( 'Enables adding widgets to link to other sites on multisites.', 'multisite-promotion' ) );

		parent::__construct( 'multisite-promotion', _x( 'Multisite Promotion', 'widget name', 'multisite-promotion' ), $widget_ops );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );

		$promote_blog_id = $instance['promote_blog_id'];

		print $before_widget;
		switch_to_blog( $promote_blog_id );
		$promote_domain = parse_url( get_option( 'home' ) );

		print '<div class="promote-blog host-' . str_replace( '.', '-', $promote_domain['host'] ) . '">';
		print '<a href="' . get_bloginfo( 'url' ) . '">';
			print $before_title . get_bloginfo( 'name' ) . $after_title;
			print "<p>" . str_replace( "//", "", get_bloginfo( 'description' ) ) . "</p>";
		print '</a></div>';

		restore_current_blog( );
		print $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['promote_blog_id'] = strip_tags( $new_instance['promote_blog_id'] );
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'promote_blog_id' ); ?>"><?php _e( 'Blog to promote', 'multisite-promotion' ); ?><br />
				<select class="widefat" id="<?php echo $this->get_field_id( 'promote_blog_id' ); ?>" name="<?php echo $this->get_field_name( 'promote_blog_id' ); ?>">
					<?php
						global $wpdb;
						$blogs = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->blogs . ' WHERE public = 1 ORDER BY domain ASC', OBJECT );

						foreach( $blogs as $blog ) {
							if( $blog->blog_id == $instance['promote_blog_id'] ) { $is_selected = ' selected="selected " '; } else { unset( $is_selected ); }
							switch_to_blog( $blog->blog_id );
							print '<option value="' . $blog->blog_id . '"' . $is_selected . '>' . get_bloginfo( 'name' ) . '</option>';
							restore_current_blog( );
						}
					?>
				</select>
			</label>
		</p>
		<?php 
	}
}

?>
