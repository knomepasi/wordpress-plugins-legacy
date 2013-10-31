<?php
/*
 *  Plugin Name: One Year Earlier
 *  Description: Show one article from one year earlier.
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

add_action( 'plugins_loaded', 'OneYearEarlierInit' );

function SimpleFBLikeInit( ) {
	/* Load text domain for i18n */
	load_plugin_textdomain( 'one-year-earlier', false, dirname( plugin_basename( FILE ) ) . '/languages/' );
}

/*  Widget
 *
 */

add_action( 'widgets_init', function( ) { register_widget( 'OneYearEarlierWidget' ); } );

class OneYearEarlierWidget extends WP_Widget {
	/** constructor */
	function __construct() {
		$widget_ops = array( 'description' => __( 'Show one article from one year earlier.', 'one-year-earlier' ) );

		parent::__construct( 'one-year-earlier', _x( 'One Year Earlier', 'widget name', 'one-year-earlier' ), $widget_ops );
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

/*  Filter
 *
 */

function OneYearEarlierFilterWhere( $where = '' ) {
	$datelimit = date( "Y" ) - 1 . date( "m" ) . date( "d" );
	$where .= " AND post_date < '$datelimit' AND post_type = 'post' AND post_status = 'publish'";
	return $where;
}

?>
