<?php
/*
 *  Plugin Name: Category Excerpts
 *  Description: Show newest excerpts from a defined category.
 *  Author: Pasi Lallinaho
 *  Version: 1.0
 *  Author URI: http://open.knome.fi/
 *  Plugin URI: http://wordpress.knome.fi/
 *
 */

/*  Init plugin
 *
 */

add_action( 'plugins_loaded', 'CategoryExcerptsInit' );

function CategoryExcerptsInit( ) {
	/* Load text domain for i18n */
	load_plugin_textdomain( 'category-excerpts', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

/*  Widget
 *
 */

add_action( 'widgets_init', function( ) { register_widget( 'CategoryExcerptsWidget' ); } );

class CategoryExcerptsWidget extends WP_Widget {
	/** constructor */
	function __construct( ) {
		$widget_ops = array( 'description' => __( 'Show newest excerpts from a defined category.', 'category-excerpts' ) );

		parent::__construct( 'category_excerpts', _x( 'Category Excerpts', 'widget name', 'category-excerpts' ), $widget_ops );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo '<div class="cat-exc">';
		echo $before_widget;

		if( !empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		# show excerpts
		if( $amount == 0 ) { $amount = 3; }
		$excq = new WP_Query( 'cat=' . $instance['category'] . '&posts_per_page=' . $instance['amount'] );

		while( $excq->have_posts( ) ) {
			$excq->the_post( );
			if( !$prev ) { $first = " first"; $prev = 1; } else { $first = ""; }

			print '<div class="cat-exc-item">';
				print '<h3 class="excerpt_title' . $first . '">' . get_the_title( ) . '</h3>';
				print '<p class="excerpt">' . get_the_excerpt( ) . '</p>';
				print '<div class="more"><a href="' . get_permalink( ) . '">' . __( 'Read more', 'category-excerpts' ) . '&nbsp;&raquo;</a></div>';
			print '</div>';

		}

		print '<p class="more-link"><strong><a href="' . get_category_link( $instance['category'] ) . '">' . __( $instance['morelink'], 'category-excerpts' ) . '&nbsp;&raquo;</a></strong></p>';

		echo $after_widget;
		echo '</div>';
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['category'] = strip_tags( $new_instance['category'] );
		$instance['amount'] = strip_tags( $new_instance['amount'] );
		$instance['morelink'] = strip_tags( $new_instance['morelink'] );

		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
		$category = esc_attr( $instance['category'] );
		$amount = esc_attr( $instance['amount'] );
		$morelink = esc_attr( $instance['morelink'] );

		if( $amount < 1 ) { $amount = 3; }

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'category-excerpts' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e( 'Category', 'category-excerpts' ); ?><br />
				<select class="widefat" id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>">
					<?php # wp_dropdown_categories( ); ?>
					<?php
						$cats = get_categories( );

						$all = new stdClass( );
						$all->name = __( '-- All categories --', 'category-excerpts' );
						$all->term_id = 0;

						array_unshift( $cats, $all );

						foreach( $cats as $cat ) {
							if( $cat->term_id == $category ) { $is_selected = ' selected="selected " '; } else { unset( $is_selected ); }
							print '<option value="' . $cat->term_id . '"' . $is_selected . '>' . $cat->name . '</option>';
						}
					?>
				</select>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'amount' ); ?>"><?php _e( 'Number of excerpts', 'category-excerpts' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'amount' ); ?>" name="<?php echo $this->get_field_name( 'amount' ); ?>" type="text" value="<?php echo $amount; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'morelink' ); ?>"><?php _e( "'More articles' link text", 'category-excerpts' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'morelink' ); ?>" name="<?php echo $this->get_field_name( 'morelink' ); ?>" type="text" value="<?php echo $morelink; ?>" />
		</p>
		<?php 
	}

}

?>
