<?php
/*
 *  Plugin Name: Taxonomy Promotion
 *  Description: Shows titles or excerpts in the selected taxonomy and term.
 *  Author: Pasi Lallinaho
 *  Version: 2.1
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

add_action( 'plugins_loaded', 'TaxonomyPromotionInit' );

function TaxonomyPromotionInit( ) {
	/* Load text domain for i18n */
	load_plugin_textdomain( 'taxonomy-promotion', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

/*  Widget
 *
 */

add_action( 'widgets_init', function( ) { register_widget( 'TaxonomyPromotionWidget' ); } );

class TaxonomyPromotionWidget extends WP_Widget {
	/** constructor */
	function __construct( ) {
		$widget_ops = array( 'description' => __( 'Shows titles or excerpts in the selected taxonomy and term.', 'taxonomy-promotion' ) );

		parent::__construct( 'taxonomy_promotion', _x( 'Taxonomy Promotion', 'widget name', 'taxonomy-promotion' ), $widget_ops );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo '<div class="taxpromo">';
		echo $before_widget;

		if( !empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		if( $amount < 1 ) { $amount = 0; }

		$tax_query['relation'] = 'OR';
		foreach( explode( ',', $instance['taxonomy_terms'] ) as $term ) {
			$term = explode( ':', $term );
			$tax_query[] = array(
				'taxonomy' => $term[0],
				'field' => 'id',
				'terms' => $term[1]
			);
		}

		$tp_query = new WP_Query( array(
			'posts_per_page' => $instance['amount'],
			'offset' => $instance['offset'],
			'tax_query' => $tax_query
		) );

		if( $instance['display'] == 'title' ) {
			echo '<ul>';
		}

		while( $tp_query->have_posts( ) ) {
			$tp_query->the_post( );

			switch( $instance['display'] ) {
				case 'title_excerpt':
					echo '<div class="item">';
					echo '<strong class="title">' . get_the_title( ) . '</strong>';
					echo '<p class="excerpt">' . get_the_excerpt( );
					echo '<br /><span class="more"><a href="' . get_permalink( ) . '">' . __( 'Read more &raquo;', 'taxonomy-promotion' ) . '</a></span>';
					echo '</p>';
					echo '</div>';
				break;
				case 'featured_title_excerpt':
					echo '<div class="item">';
					echo '<div class="featured">' . get_the_post_thumbnail( ) . '</div>';
					echo '<strong class="title">' . get_the_title( ) . '</strong>';
					echo '<p class="excerpt">' . get_the_excerpt( );
					echo '<br /><span class="more"><a href="' . get_permalink( ) . '">' . __( 'Read more &raquo;', 'taxonomy-promotion' ) . '</a></span>';
					echo '</p>';
					echo '</div>';
				break;
				case 'title':
				default:
					echo '<li class="title"><a href="' . get_permalink( ) . '">' . get_the_title( ) . '</a></li>';
				break;
			}
		}

		if( $instance['display'] == 'title' ) {
			echo '</ul>';
		}

		echo '<p class="more-link"><strong><a href="' . get_term_link( (int) $instance['taxonomy_term'], $instance['taxonomy'] ) . '">' . $instance['morelink'] . '</a></strong></p>';

		echo $after_widget;
		echo '</div>';

		wp_reset_postdata( );
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		if( $new_instance['taxonomy_and_term'] ) {
			list( $new_instance['taxonomy'], $new_instance['taxonomy_term'] ) = explode( '.', $new_instance['taxonomy_and_term'] );
		}

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['taxonomy'] = strip_tags( $new_instance['taxonomy'] );
		$instance['taxonomy_terms'] = implode( ',', $new_instance['taxonomy_terms'] );
		$instance['display'] = strip_tags( $new_instance['display'] );
		$instance['amount'] = strip_tags( $new_instance['amount'] );
		$instance['offset'] = strip_tags( $new_instance['offset'] );
		$instance['morelink'] = strip_tags( $new_instance['morelink'] );

		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
		$taxonomy = esc_attr( $instance['taxonomy'] );
		$taxonomy_terms = explode( ',', $instance['taxonomy_terms'] );
		$display = esc_attr( $instance['display'] );
		$amount = esc_attr( $instance['amount'] );
		$offset = esc_attr( $instance['offset'] );
		$morelink = esc_attr( $instance['morelink'] );

		if( $amount < 1 ) { $amount = 0; }

		?>
		<p><!-- Title -->
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'taxonomy-promotion' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<!-- Taxonomy and term -->
		<p class="taxpromo">
			<label for="<?php echo $this->get_field_id( 'taxonomy_terms' ); ?>"><?php _e( 'Terms', 'taxonomy-promotion' ); ?><br />
				<select class="widefat" multiple="multiple" id="<?php echo $this->get_field_id( 'taxonomy_terms' ); ?>" name="<?php echo $this->get_field_name( 'taxonomy_terms' ); ?>[]" <?php echo $disabled; ?> style="margin-top: 0.2em; height: 10em;">
					<?php
						$args = array( 'public' => true, 'show_ui' => true );
						$taxs = get_taxonomies( $args, 'objects' );
						foreach( $taxs as $tax ) {
							foreach( get_terms( $tax->name ) as $term ) {
								$val = $tax->name . ':' . $term->term_id;
								if( in_array( $val, $taxonomy_terms ) ) { $sel = true; } else { $sel = $false; }

								print '<option value="' . $val . '"' . selected( $sel, true, false ) . '>' . $tax->labels->singular_name . ' – ' . $term->name . '</option>';
							}
						}
					?>
				</select>
			</label>
		</p>

		<p><!-- What to display? -->
			<label for="<?php echo $this->get_field_id( 'display' ); ?>"><?php _e( 'Display', 'taxonomy-promotion' ); ?><br />
				<select class="widefat" id="<?php echo $this->get_field_id( 'display' ); ?>" name="<?php echo $this->get_field_name( 'display' ); ?>">
					<option value="title" <?php selected( $display, 'title' ); ?>><?php _e( 'Title', 'taxonomy-promotion' ); ?></option>
					<option value="title_excerpt" <?php selected( $display, 'title_excerpt' ); ?>><?php _e( 'Title, Excerpt', 'taxonomy-promotion' ); ?></option>
					<?php if( current_theme_supports( 'post-thumbnails' ) ) { ?>
						<option value="featured_title_excerpt" <?php selected( $display, 'featured_title_excerpt' ); ?>><?php _e( 'Featured image, Title, Excerpt', 'taxonomy-promotion' ); ?></option>
					<?php } ?>
				</select>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'amount' ); ?>"><?php _e( 'Item count', 'taxonomy-promotion' ); ?></label> <input id="<?php echo $this->get_field_id( 'amount' ); ?>" name="<?php echo $this->get_field_name( 'amount' ); ?>" type="text" value="<?php echo $amount; ?>" size="3" />
			<label for="<?php echo $this->get_field_id( 'offset' ); ?>"><?php _e( 'Offset', 'taxonomy-promotion' ); ?></label> <input id="<?php echo $this->get_field_id( 'offset' ); ?>" name="<?php echo $this->get_field_name( 'offset' ); ?>" type="text" value="<?php echo $offset; ?>" size="3" />
			<br /><small><?php _e( 'Set count to 0 for all items.', 'taxonomy-promotion' ); ?></small>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'morelink' ); ?>"><?php _e( "Text for 'more items' link", 'taxonomy-promotion' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'morelink' ); ?>" name="<?php echo $this->get_field_name( 'morelink' ); ?>" type="text" value="<?php echo $morelink; ?>" />
			<br /><small><?php _e( '<b>Only if number of items is more than 0.</b> If you leave this field empty, no link will be shown.', 'taxonomy-promotion' ); ?></small>
		</p>
		<?php 
	}

}

?>
