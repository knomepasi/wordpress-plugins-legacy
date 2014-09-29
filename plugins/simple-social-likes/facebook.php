<?php

/*  Shortcode
 *
 */

add_shortcode( 's-fb-like', 'SimpleSocialLikesFBShortcode' );

function SimpleSocialLikesFBShortcode( $atts, $content, $code ) {
	extract( shortcode_atts( array(
		'id' => '',
	), $atts ) );

	return _SimpleSocialLikes_IFrame( 'fb', $id );
}

/*  Widget
 *
 */

add_action( 'widgets_init', function( ) { register_widget( 'SimpleSocialLikes_FBWidget' ); } );

class SimpleSocialLikes_FBWidget extends WP_Widget {
	/** constructor */
	function __construct() {
		$widget_ops = array( 'description' => __( 'Shows a Facebook Like button for a Facebook profile.', 'simple-social-likes' ) );

		parent::__construct( 'simple-fb-like', _x( 'Facebook Like button', 'widget name', 'simple-social-likes' ), $widget_ops );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if( $instance['title'] ) { print $before_title . $title . $after_title; }
		print _SimpleSocialLikes_IFrame( 'fb', $instance['profile'] );
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
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'simple-social-likes' ); ?><br />
				<input style="width: 220px;" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'profile' ); ?>"><?php _e( 'Profile name', 'simple-social-likes' ); ?><br />
				<input style="width: 220px;" id="<?php echo $this->get_field_id( 'profile' ); ?>" name="<?php echo $this->get_field_name( 'profile' ); ?>" type="text" value="<?php echo $profile; ?>" />
			</label>
		</p>

		<?php
	}
}

?>