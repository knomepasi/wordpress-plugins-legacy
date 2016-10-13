<?php
/*
 *  Plugin Name: Network Blog Promotion
 *  Description: A widget to promote other blogs on a network installation.
 *  Author: Pasi Lallinaho
 *  Version: 1.2.1
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

add_action( 'plugins_loaded', 'network_blog_promotion_init' );

function network_blog_promotion_init( ) {
	load_plugin_textdomain( 'network-blog-promotion', false, dirname( plugin_basename( FILE ) ) . '/languages/' );
}

/*
 *  Register the widget
 *
 */

add_action( 'widgets_init', function( ) { register_widget( 'network_blog_promotion_Widget' ); } );

class network_blog_promotion_Widget extends WP_Widget {
	function __construct() {
		parent::__construct(
			'network-blog-promotion',
			_x( 'Network Blog Promotion', 'widget name', 'network-blog-promotion' ),
			array(
				'description' => __( 'Promote a blog on the same WordPress network.', 'network-blog-promotion' ),
			)
		);
	}

	function widget( $args, $instance ) {
		extract( $args );
		$promote_blog_id = $instance['promote_blog_id'];

		echo $before_widget;
		switch_to_blog( $promote_blog_id );
		$promote_domain = parse_url( get_option( 'home' ) );

		echo '<div class="promote-blog host-' . str_replace( '.', '-', $promote_domain['host'] ) . '">';
		echo '<a href="' . get_bloginfo( 'url' ) . '">';
			echo $before_title . get_bloginfo( 'name' ) . $after_title;
			echo '<p class="blog_description">' . get_bloginfo( 'description' ) . '</p>';
			echo '<p class="promotion_description">' . $instance['description'] . '</p>';
		echo '</a></div>';

		restore_current_blog( );
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['promote_blog_id'] = strip_tags( $new_instance['promote_blog_id'] );
		$instance['description'] = strip_tags( $new_instance['description'] );
		return $instance;
	}

	function form( $instance ) {
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'promote_blog_id' ); ?>"><?php _e( 'Blog to promote', 'network-blog-promotion' ); ?><br />
				<select class="widefat" id="<?php echo $this->get_field_id( 'promote_blog_id' ); ?>" name="<?php echo $this->get_field_name( 'promote_blog_id' ); ?>">
					<?php
						global $wpdb;
						$blogs = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->blogs . ' WHERE public = 1 ORDER BY domain ASC', OBJECT );

						foreach( $blogs as $blog ) {
							$blog_details = get_blog_details( $blog->blog_id );
							echo '<option value="' . $blog->blog_id . '"' . selected( $instance['promote_blog_id'], $blog->blog_id ) . '>' . $blog_details->blogname . '</option>';
						}
					?>
				</select>
			</label>

			<label for="<?php echo $this->get_field_id( 'description' ); ?>"><?php _e( 'Description', 'network-blog-promotion' ); ?>
				<textarea class="widefat" id="<?php echo $this->get_field_id( 'description' ); ?>" name="<?php echo $this->get_field_name( 'description' ); ?>"><?php echo $instance['description']; ?></textarea>
			</label>

		</p>
		<?php 
	}
}

?>
