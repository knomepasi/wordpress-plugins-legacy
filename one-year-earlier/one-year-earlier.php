<?php
/*
 *  Plugin Name: One Year Earlier
 *  Description: Show one article from one year earlier.
 *  Author: Pasi Lallinaho
 *  Version: 0.1
 *  Author URI: http://open.knome.fi/
 *  Plugin URI: https://github.com/knomepasi/WordPress-plugins
 *
 */

add_action( 'widgets_init', create_function( '', 'return register_widget("YearEarlierWidget");' ) );

class OneYearEarlierWidget extends WP_Widget {
	/** constructor */
	function YearEarlierWidget() {
		$ops = array( "description" => __( 'Show one article from one year earlier.', 'one-year-earlier' ) );
		parent::WP_Widget( false, $name = __( 'One Year Earlier', 'one-year-earlier' ), $ops );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if( $title ) { echo $before_title . $title . $after_title; }
		/* */
		global $wpdb, $wp_locale;

		add_filter( 'posts_where', 'OneYearEarlierFilterWhere' );
		$ly_query = new WP_Query( "posts_per_page=1" );
		remove_filter( 'posts_where', 'OneYearEarlierFilterWhere' );

		if( $ly_query->have_posts( ) ) {
			while( $ly_query->have_posts( ) ) {
				$ly_query->the_post( );

				print "<div class=\"year_ago group\">";
				print "<p>";
				print "<strong><a href='" . get_permalink( ) . "'>" . get_the_title( ) . "</a></strong><br />";
				print "<span class='excerpt'>" . get_the_excerpt( ) . "</span>";
				print "</p>";
				print "</div>\n";
			}
		}
		wp_reset_postdata( );

		/* */
		echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'one-year-earlier' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
			</label>
		</p>
		<?php 
	}
}

function OneYearEarlierFilterWhere( $where = '' ) {
	$datelimit = date( "Y" ) - 1 . date( "m" ) . date( "d" ) + 1;
	$where .= " AND post_date < '$datelimit' AND post_type = 'post' AND post_status = 'publish'";
	return $where;
}

?>
