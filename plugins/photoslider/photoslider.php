<?php
/*
 *  Plugin Name: Photoslider
 *  Description: Show a slideshow of user uploaded photos.
 *  Author: Pasi Lallinaho
 *  Version: 1.7.1
 *  Author URI: https://open.knome.fi/
 *  Plugin URI: https://wordpress.knome.fi/
 *
 *  License: GNU General Public License v2 or later
 *  License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 */

/*  On plugin activation, create options for default values if needed
 *
 */

register_activation_hook( __FILE__, 'PhotosliderActivate' );

function PhotosliderActivate( ) {
	add_option( 'photoslider_default_size', 'medium' );
	add_option( 'photoslider_previous_slide_string', '&laquo;' );
	add_option( 'photoslider_next_slide_string', '&raquo;' );
}

/*  Init plugin
 *
 */

add_action( 'plugins_loaded', 'PhotosliderInit' );

function PhotosliderInit( ) {
	/* Load text domain for i18n */
	load_plugin_textdomain( 'photoslider', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

/*  Include scripts and default stylesheets
 *
 */

add_action( 'wp_enqueue_scripts', 'PhotosliderScripts' );

function PhotosliderScripts( ) {
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-effects-slide' );

	wp_register_script( 'photoslider', plugins_url( dirname( plugin_basename( __FILE__ ) ) . '/slider.js' ), array ( 'jquery' ), '1.4' );
	wp_enqueue_script( 'photoslider' );

	wp_register_style( 'photoslider-defaults', plugins_url( dirname( plugin_basename( __FILE__ ) ) . '/defaults.css' ) );
	wp_enqueue_style( 'photoslider-defaults' );
}

/*  Widget
 *
 */

add_action( 'widgets_init', function( ) { register_widget( 'PhotosliderWidget' ); } );

class PhotosliderWidget extends WP_Widget {
	/** constructor */
	function __construct() {
		$widget_ops = array( 'description' => __( 'Show a slideshow of user uploaded photos.', 'photoslider' ) );

		parent::__construct( 'photoslider', _x( 'Photoslider', 'widget name', 'photoslider' ), $widget_ops );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$attachments = PhotosliderAttachments( $instance['custom_id'], $instance['orderby'], $instance['order'] );

		echo $before_widget;
		if( $instance['title'] ) { $title = $before_title . $title . $after_title; }
		if( $attachments ) { echo GetPhotoslider( $instance, $attachments, $title ); }
		echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['size'] = strip_tags( $new_instance['size'] );
		$instance['instance_id'] = strip_tags( $new_instance['instance_id'] );
		$instance['controls'] = $new_instance['controls'];
		$instance['transition'] = $new_instance['transition'];
		$instance['timeout'] = (int) $new_instance['timeout'];
		$instance['captions'] = (int) $new_instance['captions'];
		$instance['orderby'] = $new_instance['orderby'];
		$instance['order'] = $new_instance['order'];
		$instance['custom_id'] = (int) $new_instance['custom_id'];
		$instance['url'] = $new_instance['url'];
		$instance['url_title'] = $new_instance['url_title'];
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
		$size = esc_attr( $instance['size'] );
		$custom_id = esc_attr( $instance['custom_id'] );
		$url = esc_attr( $instance['url'] );
		$url_title = esc_attr( $instance['url_title'] );

		if( !$size ) { $size = get_option( 'photoslider_default_size' ); }
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'photoslider' ); ?><br />
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'size' ); ?>"><?php _e( 'Image size', 'photoslider' ); ?><br />
				<select class="widefat" id="<?php echo $this->get_field_id( 'size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>">
				<?php
					global $_wp_additional_image_sizes;

					$default_sizes['thumbnail'] = array( "width" => get_option( 'thumbnail_size_w' ), "height" => get_option( 'thumbnail_size_h' ), "crop" => get_option( 'thumbnail_crop' ) );
					$default_sizes['medium'] = array( "width" => get_option( 'medium_size_w' ), "height" => get_option( 'medium_size_h' ) );
					$default_sizes['large'] = array( "width" => get_option( 'large_size_w' ), "height" => get_option( 'large_size_h' ) );

					$image_sizes = array_merge( $default_sizes, (array) $_wp_additional_image_sizes );

					foreach( $image_sizes as $name => $attr ) {
						if( $attr['crop'] == 1 ) { $is_cropped = ", cropped"; } else { unset( $is_cropped ); }
						if( $instance['size'] == $name ) { $is_selected = ' selected="selected" '; } else { unset( $is_selected ); }

						print '<option value="' . $name . '"' . $is_selected . '>' . $name . " (" . $attr['width'] . "&times;" . $attr['height'] . $is_cropped . ")</option>";
					}
				?>
				</select>
			</label>

			<label for="<?php echo $this->get_field_id( 'controls' ); ?>"><?php _e( 'Controls', 'photoslider' ); ?><br />
				<select class="widefat" id="<?php echo $this->get_field_id( 'controls' ); ?>" name="<?php echo $this->get_field_name( 'controls' ); ?>">
					<?php
						$c_opt = array(
							"none" => _x( "None", "controls", "photoslider" ),
							"above" => _x( "Above", "controls", "photoslider" ),
							"ontop" => _x( "On top", "controls", "photoslider" )
						);
						foreach( $c_opt as $id => $name ) {
							if( $id == $instance['controls'] ) { $is_selected = ' selected="selected " '; } else { unset( $is_selected ); }
							print '<option value="' . $id . '"' . $is_selected . '>' . $name . '</option>';
						}
					?>
				</select>
			</label>

			<label for="<?php echo $this->get_field_id( 'transition' ); ?>"><?php _e( 'Transition type', 'photoslider' ); ?><br />
				<select class="widefat" id="<?php echo $this->get_field_id( 'transition' ); ?>" name="<?php echo $this->get_field_name( 'transition' ); ?>">
					<?php
						$to_opt = array(
							'fade' => _x( 'Fade', 'transition type', 'photoslider' ),
							'fadefast' => _x( 'Fade (fast)', 'transition type', 'photoslider' ),
							'slideleft' => _x( 'Slide (towards left)', 'transition type', 'photoslider' ),
							'slideright' => _x( 'Slide (towards right)', 'transition type', 'photoslider' ),
						);
						foreach( $to_opt as $id => $name ) {
							if( $id == $instance['transition'] ) { $is_selected = ' selected="selected " '; } else { unset( $is_selected ); }
							print '<option value="' . $id . '"' . $is_selected . '>' . $name . '</option>';
						}
					?>
				</select>
			</label>

			<label for="<?php echo $this->get_field_id( 'timeout' ); ?>"><?php _e( 'Time between transitions', 'photoslider' ); ?><br />
				<select class="widefat" id="<?php echo $this->get_field_id( 'timeout' ); ?>" name="<?php echo $this->get_field_name( 'timeout' ); ?>">
					<?php
						$to_opt = array(
							"0" => __( "Don't advance automatically", "photoslider" ),
							"6500" => _x( "Fast", "transition speed", "photoslider" ),
							"9000" => _x( "Default", "transition speed", "photoslider" ),
							"12000" => _x( "Slow", "transition speed", "photoslider" )
						);
						foreach( $to_opt as $id => $name ) {
							if( $id == $instance['timeout'] ) { $is_selected = ' selected="selected " '; } else { unset( $is_selected ); }
							print '<option value="' . $id . '"' . $is_selected . '>' . $name . '</option>';
						}
					?>
				</select>
			</label>

			<?php _e( 'Show captions?', 'photoslider' ); ?><br />
			<?php if( $instance['captions'] < 1 ) { $capt_no = ' checked="checked" '; } else { $capt_yes = ' checked="checked" '; } ?>
			<input type="radio" id="<?php echo $this->get_field_id( 'captions_yes' ); ?>" name="<?php echo $this->get_field_name( 'captions' ); ?>" value="1" <?php echo $capt_yes; ?> />
			<label for="<?php echo $this->get_field_id( 'captions_yes' ); ?>"><?php _e( "Yes", "photoslider" ); ?></label>&nbsp;&nbsp;&nbsp;
			<input type="radio" id="<?php echo $this->get_field_id( 'captions_no' ); ?>" name="<?php echo $this->get_field_name( 'captions' ); ?>" value="0" <?php echo $capt_no; ?> />
			<label for="<?php echo $this->get_field_id( 'captions_no' ); ?>"><?php _e( "No", "photoslider" ); ?></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'orderby' ); ?>"><?php _e( 'Order by', 'photoslider' ); ?><br />
				<select class="widefat" id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>">
					<?php
						$order_opt = array(
							'title' => _x( 'Title', 'order by option', 'photoslider' ),
							'date' => _x( 'Date', 'order by option', 'photoslider' ),
							'name' => _x( 'Filename', 'order by option', 'photoslider' ),
							'rand' => _x( 'Random', 'order by option', 'photoslider' )
						);
						foreach( $order_opt as $id => $name ) {
							if( $id == $instance['orderby'] ) { $is_selected = ' selected="selected " '; } else { unset( $is_selected ); }
							print '<option value="' . $id . '"' . $is_selected . '>' . $name . '</option>';
						}
					?>
				</select>
			</label>

			<?php _e( 'Order', 'photoslider' ); ?><br />
			<?php if( $instance['order'] == "ASC" ) { $order_asc = ' checked="checked" '; } else { $order_desc = ' checked="checked" '; } ?>
			<input type="radio" id="<?php echo $this->get_field_id( 'order_asc' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>" value="ASC" <?php echo $order_asc; ?> />
			<label for="<?php echo $this->get_field_id( 'order_asc' ); ?>"><?php _e( "Ascending", "photoslider" ); ?></label>&nbsp;&nbsp;&nbsp;
			<input type="radio" id="<?php echo $this->get_field_id( 'order_desc' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>" value="DESC" <?php echo $order_desc; ?> />
			<label for="<?php echo $this->get_field_id( 'order_desc' ); ?>"><?php _e( "Descending", "photoslider" ); ?></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'custom_id' ); ?>"><?php _e( 'Page or post ID (leave blank for current)', 'photoslider' ); ?><br />
				<input class="widefat" id="<?php echo $this->get_field_id( 'custom_id' ); ?>" name="<?php echo $this->get_field_name( 'custom_id' ); ?>" type="text" value="<?php echo $custom_id; ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'url' ); ?>"><?php _e( 'URL to redirect to when clicking outside the controls', 'photoslider' ); ?><br />
				<input class="widefat" id="<?php echo $this->get_field_id( 'url' ); ?>" name="<?php echo $this->get_field_name( 'url' ); ?>" type="text" value="<?php echo $url; ?>" />
			</label>
			<label for="<?php echo $this->get_field_id( 'url_title' ); ?>"><?php _e( 'URL title text', 'photoslider' ); ?><br />
				<input class="widefat" id="<?php echo $this->get_field_id( 'url_title' ); ?>" name="<?php echo $this->get_field_name( 'url_title' ); ?>" type="text" value="<?php echo $url_title; ?>" />
			</label>
		</p>

		<input type="hidden" name="<?php echo $this->get_field_name( 'instance_id' ); ?>" value="<?php echo uniqid( 'photoslider_' ); ?>" />

		<?php 
	}
}

/*  Add shortcode
 *
 */

add_shortcode( 'photoslider', 'PhotosliderShortcode' );

function PhotosliderShortcode( $atts, $content, $code ) {
	$slider_opts = shortcode_atts( array(
		'size' => 'medium',
		'controls' => 'none',
		'transition' => 'fade',
		'timeout' => 8000,
		'captions' => 'no',
		'orderby' => 'date',
		'orderdir' => 'ASC',
		'post' => 0,
		'url' => null,
		'mode' => false
	), $atts );

	$slider_opts['instance_id'] = uniqid( 'photoslider_' );

	if( !isset( $atts['post'] ) ) { $atts['post'] = 0; }
	if( !isset( $atts['orderby'] ) ) { $atts['orderby'] = 'date'; }
	if( !isset( $atts['orderdir'] ) ) { $atts['orderdir'] = 'ASC'; }

	$attachments = PhotosliderAttachments( $atts['post'], $atts['orderby'], $atts['orderdir'] );
	if( $attachments ) { $out = GetPhotoslider( $slider_opts, $attachments, null ); }

	return $out;
}

/*  Function that actually outputs the sliders
 *
 */

Function GetPhotoslider( $opts, $attachments, $title ) {
	/* determine photo size */
	if( !$opts['size'] ) { $opts['size'] = get_option( 'photoslider_default_size' ); }

	/* determine exact dimensions for first photo for non-js users */
	$first_item = array_shift( array_values( $attachments ) );
	$first_attr = wp_get_attachment_image_src( $first_item->ID, $opts['size'] );
	$first_dimensions = 'style="width: ' . $first_attr[1] . 'px; height: ' . $first_attr[2] . 'px;"';

	/* start wrapping div */
	$output = '<div class="ps_wrap" ' . $first_dimensions . '>';
	if( $opts['url'] ) {
		$output .= '<a class="ps_link" href="' . $opts['url'] . '" title="' . $opts['url_title'] . '">';
	}
	$output .= '<div class="photoslider ctrl-' . $opts['controls'] . '" id="' . $opts['instance_id'] . '">';

		$output .= '<div class="title">' . $title . '</div>';
		$output .= '<ul>';

		$is_first = TRUE;
		foreach( $attachments as $a ) {
			if( $is_first ) {
				$output .= '<li class="first active">';
				$is_first = FALSE;
			} else {
				$output .= '<li>';
			}

			$output .= '<div class="image">';
			$output .= wp_get_attachment_image( $a->ID, $opts['size'] );
			$output .= '</div>';

			if( $opts['captions'] == true ) {
				$output .= '<div class="captions">';
				$output .= '<p class="caption-title">' . $a->post_title . '</p>';
				if( $a->post_content ) {
					$output .= '<p class="caption-content">' . $a->post_content  . '</p>';
				}
				$output .= '</div>';
			}

			$output .= '</li>';
		}

		$output .= '</ul>';

		if( $opts['controls'] != "none" ) {
			$output .= '<div class="controls ' . $opts['controls'] . '">';
			$output .= '<a href="#" class="c-prev" title="' . __( 'Previous', 'photoslider' ) . '">' . get_option( 'photoslider_previous_slide_string', 'photoslider' ) . '</a>';
			$output .= '<a href="#" class="c-next" title="' . __( 'Next', 'photoslider' ) . '">' . get_option( 'photoslider_next_slide_string', 'photoslider' ) . '</a>';
		$output .= '</div>';
		}

	$output .= '</div>';
	if( $opts['url'] ) {
		$output .= '</a>';
	}
	$output .= '</div>';

	$output .= PhotosliderScriptsDynamic( $opts );

	return $output;
}

/*  Add a helper function that writes per-slider options
 *
 */

function PhotosliderScriptsDynamic( $args ) {
	$dimensions = explode( 'x', $args['size'] );
	if( intval( $dimensions[0] ) == 0 || intval( $dimensions[1] ) == 0 ) {
		if( in_array( $args['size'], array( 'thumbnail', 'medium', 'large' ) ) ) {
			$args['size'] = get_option( $args['size'] . '_size_w' ) . 'x' . get_option( $args['size'] . '_size_h' );
		} else {
			global $_wp_additional_image_sizes;
			$args['size'] = $_wp_additional_image_sizes[ $args['size'] ]['width'] . 'x' . $_wp_additional_image_sizes[ $args['size'] ]['height'];
		}
	}

	if( strlen( $args['size'] ) == 1 ) {
		$args['size'] = get_option( 'medium_size_w' ) . 'x' . get_option( 'medium_size_h' );
	}

	$out  = '<script type="text/javascript">';
	$out .= 'jQuery( window ).load( function( ) {';

	$out .= 'var ' . $args['instance_id'] . ' = ' . "\n";
	$out .= '{ "id": "' . $args['instance_id'] . '", "controls": "' . $args['controls'] . '", "timeout": "' . $args['timeout'] . '", "transition": "' . $args['transition'] . '", "size": "' . $args['size'] . '", "mode": "' . $args['mode'] . '" } ' . "\n";
	$out .= '; ';

	$out .= 'runPhotoslider( ' . $args['instance_id'] . ' );';

	$out .= '} );';
	$out .= '</script>';

	return $out;
}

/*  Add a helper function that determines the attachments wanted for a slider
 *
 */

function PhotosliderAttachments( $id, $order_by = 'date', $order_direction = 'DESC' ) {
	if( !$id || $id < 1 ) {
		$post_id = get_option( 'page_on_front' );
	} elseif( $id > 0 ) {
		$post_id = $id;
	} else {
		global $post;
		$post_id = $post->ID;
	}

	$args = array(
		'post_parent' => $post_id,
		'post_type' => 'attachment',
		'post_mime_type' => 'image',
		'order_by' => $order_by,
		'order' => $order_direction
	);

	$attachments = get_children( $args );

	return $attachments;
}

?>