<?php
/*
 *  Plugin Name: Flashback
 *  Description: A widget to promote articles from the past.
 *  Author: Pasi Lallinaho
 *  Version: 1.2.1
 *  Author URI: https://open.knome.fi/
 *  Plugin URI: https://wordpress.knome.fi/
 *
 *  License: GNU General Public License v2 or later
 *  License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 */

/*
 *  Load text domain for translations
 *
 */

add_action( 'plugins_loaded', 'flashback_init' );

function flashback_init( ) {
	load_plugin_textdomain( 'flashback', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

/*  Widget
 *
 */

add_action( 'widgets_init', function( ) { register_widget( 'flashback_Widget' ); } );

class flashback_Widget extends WP_Widget {
	function __construct() {
		parent::__construct(
			'flashback',
			_x( 'Flashback', 'widget name', 'flashback' ),
			array(
				'description' => __( 'Promote articles from the past.', 'flashback' ),
			)
		);
	}

	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if( $title ) { echo $before_title . $title . $after_title; }

		global $wpdb, $wp_locale;

		if( !$instance['age_of_post'] ) { $instance['age_of_post'] == 365; }
		$flasback_target = explode( '/', gmdate( 'Y/n/j', time( ) - ( 86400 * $instance['age_of_post'] ) ) );

		$flashback_query = new WP_Query( array(
			'posts_per_page' => '1',
			'date_query' => array(
				array(
					'before' => array(
						'year' => $flasback_target[0],
						'month' => $flasback_target[1],
						'day' => $flasback_target[2]
					),
					'inclusive' => true
				)
			)
		) );

		if( $flashback_query->have_posts( ) ) {
			while( $flashback_query->have_posts( ) ) {
				$flashback_query->the_post( );

				echo '<div class="flashback group">';
				echo '<p>';
				echo '<strong><a href="' . get_permalink( ) . '">' . get_the_title( ) . '</a></strong><br />';
				echo '<span class="excerpt">' . get_the_excerpt( ) . '</span>';
				echo '</p>';
				echo '</div>';
			}
		}
		wp_reset_postdata( );

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['age_of_post'] = $new_instance['age_of_post'];
		return $instance;
	}

	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'flashback' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
			</label>
			<label for="<?php echo $this->get_field_id( 'age_of_post' ); ?>"><?php _e( 'Minimum age of post', 'flashback' ); ?><br />
				<select class="widefat" id="<?php echo $this->get_field_id( 'age_of_post' ); ?>" name="<?php echo $this->get_field_name( 'age_of_post' ); ?>">
					<?php
						$opts = array(
							'365' => __( 'Year', 'flashback' ),	
							'30' => __( 'Month', 'flashback' ),
							'7' => __( 'Week', 'flashback' )
						);
						foreach( $opts as $days => $name ) {
							if( $days == $instance['age_of_post'] ) { $is_selected = ' selected="selected " '; } else { unset( $is_selected ); }
							echo '<option value="' . $days . '" ' . selected( $instance['age_of_posts'], $days, false ) . '>' . $name . '</option>';
						}
					?>
				</select>
			</label>
		</p>
		<?php 
	}
}

?>
