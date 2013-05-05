<?php
/*
 *  Plugin Name: Simple FB Like
 *  Description: Shows a Facebook Like button for a Facebook profile.
 *  Author: Pasi Lallinaho
 *  Version: 1.1
 *  Author URI: http://open.knome.fi/
 *  Plugin URI: http://wordpress.knome.fi/
 *
 */

/*  Init plugin
 *
 */

add_action( 'plugins_loaded', 'SimpleFBLikeInit' );

function SimpleFBLikeInit( ) {
	/* Load text domain for i18n */
	load_plugin_textdomain( 'simple-fb-like', false, dirname( plugin_basename( FILE ) ) . '/languages/' );
}

/*  Widget
 *
 */

add_action( 'widgets_init', function( ) { register_widget( 'SimpleFBLikeWidget' ); } );

class SimpleFBLikeWidget extends WP_Widget {
	/** constructor */
	function __construct() {
		$widget_ops = array( 'description' => __( 'Shows a Facebook Like button for a Facebook profile.', 'simple-fb-like' ) );

		parent::__construct( 'simple-fb-like', _x( 'FB Like', 'widget name', 'simple-fb-like' ), $widget_ops );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if( $instance['title'] ) { $title = $before_title . $title . $after_title; }
		print _SimpleFB_IFrame( $instance['profile'] );
		echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['profile'] = strip_tags( $new_instance['profile'] );
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
		$profile = esc_attr( $instance['profile'] );
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'simple-fb-like' ); ?><br />
				<input style="width: 220px;" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'profile' ); ?>"><?php _e( 'Profile name', 'simple-fb-like' ); ?><br />
				<input style="width: 220px;" id="<?php echo $this->get_field_id( 'profile' ); ?>" name="<?php echo $this->get_field_name( 'profile' ); ?>" type="text" value="<?php echo $profile; ?>" />
			</label>
		</p>

		<?php
	}
}

/*  Add shortcode
 *
 */

add_shortcode( 'simple-fb-like', 'SimpleFBLikeShortcode' );

function SimpleFBLikeShortcode( $atts, $content, $code ) {
	extract( shortcode_atts( array(
		'fbid' => '',
	), $atts ) );

	return _SimpleFB_IFrame( $fbid );
}

/*  Helper function to print the FB-like iframe
 *
 */

function _SimpleFB_IFrame( $id ) {
	return '<iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Ffacebook.com%2F' . $fbid . '&width=292&height=62&colorscheme=light&show_faces=false&border_color&stream=false&header=false" width="292" height="24"></iframe>';
}

?>
