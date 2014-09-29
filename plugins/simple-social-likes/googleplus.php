<?php

/*  Shortcode
 *
 */

add_shortcode( 's-google-like', 'SimpleSocialLikesGoogleShortcode' );

function SimpleSocialLikesGoogleShortcode( $atts, $content, $code ) {
	extract( shortcode_atts( array(
		'id' => '',
	), $atts ) );

	return _SimpleSocialLikes_IFrame( 'google', $id );
}

/*  Widget
 *
 */

add_action( 'widgets_init', function( ) { register_widget( 'SimpleSocialLikes_GoogleWidget' ); } );

class SimpleSocialLikes_GoogleWidget extends WP_Widget {
	/** constructor */
	function __construct() {
		$widget_ops = array( 'description' => __( 'Shows a Google +1 button for the current or a specified URL.', 'simple-social-likes' ) );

		parent::__construct( 'simple-google-plusone', _x( 'Google +1 button', 'widget name', 'simple-social-likes' ), $widget_ops );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if( $instance['title'] ) { print $before_title . $title . $after_title; }
		print _SimpleSocialLikes_IFrame( 'google', $instance['url'] );
		echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['url'] = strip_tags( $new_instance['url'] );
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
		$profile = esc_attr( $instance['url'] );
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'simple-social-likes' ); ?><br />
				<input style="width: 220px;" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'url' ); ?>"><?php _e( 'URL (or blank for current)', 'simple-social-likes' ); ?><br />
				<input style="width: 220px;" id="<?php echo $this->get_field_id( 'url' ); ?>" name="<?php echo $this->get_field_name( 'url' ); ?>" type="text" value="<?php echo $profile; ?>" />
			</label>
		</p>

		<?php
	}
}

?>