<?php
/*
 *  Plugin Name: Simple Planet
 *  Description: Show posts from multiple feeds sorted by date via a widget.
 *  Author: Pasi Lallinaho
 *  Version: 1.0
 *  Author URI: http://open.knome.fi/
 *  Plugin URI: https://github.com/knomepasi/WordPress-plugins
 *
 */

/*  Plugin activation
 *
 */

register_activation_hook( __FILE__, 'SimplePlanetActivate' );

function SimplePlanetActivate( ) {
	add_option( 'simple_planet_items_default', 10 );
	add_option( 'simple_planet_refresh_default', 60 );
}

/*  Init plugin
 *
 */

add_action( 'init', 'SimplePlanetInit' );

function SimplePlanetInit( ) {
	/* Load text domain for i18n */
	load_plugin_textdomain( 'simple-planet' );
}

/*  Widget class
 *
 */

add_action( 'widgets_init', create_function( '', 'return register_widget("SimplePlanetWidget");' ) );

class SimplePlanetWidget extends WP_Widget {
	/** constructor */
	function SimplePlanetWidget( ) {
		parent::WP_Widget(
			'simple_planet',
			_x( 'Simple Planet', 'widget name', 'simple-planet' ),	
			array(
				'description' => __( 'Show aggregated posts from multiple feeds sorted by date via a widget.', 'simple-planet' ),
				'width'       => 500,
				'height'      => 400
			)
		);
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $before_widget;

		if( !empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		$time_diff = $instance['refresh_in'] * 60;
		if( ( get_option( 'simple_planet_' . $args['widget_id'] . '_lastupdate', 0 ) + $time_diff ) < time( ) ) {
			# we need to update.
			$count = 0;
			$planet = array( );

			# include simplepie
			#include_once( ABSPATH . WPINC . '/class-simplepie.php' );

			foreach( explode( "\n", $instance['feeds'] ) as $feed ) { 
				$feeds[] = rtrim( ltrim( $feed ) );
			}

			$feed = new SimplePie( );
			$feed->set_feed_url( $feeds );
			$feed->set_item_limit( $instance['items'] );
			$feed->init( );
			$feed->handle_content_type( );

			$items = $feed->get_items( 0, $instance['items'] );
	
			if( is_array( $items ) ) {
				# rss feed has items
				foreach( $items as $item ) {
					$item_id = $item->get_local_date( '%s' ) . "-" . $count;
					$planet[$item_id] = array(
						"title" => $item->get_title( ),
						"link" => $item->get_permalink( ),
						"author" => $item->get_author( )->get_name( )
					);

					$count++;
				}
			}

			if( is_array( $planet ) ) {
				krsort( $planet );
			}

			update_option( 'simple_planet_' . $args['widget_id'] . '_lastupdate', time( ) );
			update_option( 'simple_planet_' . $args['widget_id'] . '_posts', $planet );
		}

		# show posts
		$widget_posts = get_option( 'simple_planet_' . $args['widget_id'] . '_posts' );
		if( is_array( $widget_posts ) ) {
			?>
			<ul class="simple_planet">
				<?php foreach( $widget_posts as $post ) { ?>
					<li>
						<a href="<?php echo $post['link']; ?>"><?php echo $post['title']; ?></a><br />
						<span><?php _e( 'by', 'simple-planet' ); ?> <?php echo $post['author']; ?></span>
					</li>
				<?php } ?>
			</ul>
			<?php
		}

		echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['feeds'] = strip_tags( $new_instance['feeds'] );
		if( is_numeric( $new_instance['items'] ) ) {
			$instance['items'] = floor( strip_tags( $new_instance['items'] ) );
		}
		if( is_numeric( $new_instance['refresh_in'] ) ) {
			$instance['refresh_in'] = floor( strip_tags( $new_instance['refresh_in'] ) );
		}

		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
		$feeds = esc_attr( $instance['feeds'] );
		$items = esc_attr( $instance['items'] );
		$refresh_in = esc_attr( $instance['refresh_in'] );

		if( empty( $items ) ) { $items = get_option( 'simple_planet_items_default' ); }
		if( empty( $refresh_in ) ) { $refresh_in = get_option( 'simple_planet_refresh_default' ); }
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'simple-planet' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />

			<label for="<?php echo $this->get_field_id( 'feeds' ); ?>"><?php _e( 'Feeds (one per line)', 'simple-planet' ); ?></label>
			<textarea class="widefat" id="<?php echo $this->get_field_id( 'feeds' ); ?>" name="<?php echo $this->get_field_name( 'feeds' ); ?>"><?php echo $feeds; ?></textarea>

			<label for="<?php echo $this->get_field_id( 'items' ); ?>"><?php _e( 'Items to show', 'simple-planet' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'items' ); ?>" name="<?php echo $this->get_field_name( 'items' ); ?>" type="text" value="<?php echo $items; ?>" />

			<label for="<?php echo $this->get_field_id( 'refresh_in' ); ?>"><?php _e( 'Minimum refresh interval (in minutes)', 'simple-planet' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'refresh_in' ); ?>" name="<?php echo $this->get_field_name( 'refresh_in' ); ?>" type="text" value="<?php echo $refresh_in; ?>" />
		</p>
		<?php 
	}

}

?>
