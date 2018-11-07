<?php
/*
 *  Plugin Name: Links (Inline Descriptions)
 *  Description: A links widget with inline descriptions.
 *  Author: Pasi Lallinaho
 *  Version: 1.0.1
 *  Author URI: http://open.knome.fi/
 *  Plugin URI: http://wordpress.knome.fi/
 *
 *  License: GNU General Public License v2 or later
 *  License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

/*  
 *  Load text domain for translations
 *
 */

add_action( 'plugins_loaded', 'links_inline_descriptions_init' );

function links_inline_descriptions_init( ) {
	load_plugin_textdomain( 'links-inline-descriptions', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

/*
 *  Register the widget
 *
 */

add_action( 'widgets_init', function( ) { register_widget( 'links_inline_descriptions_Widget' ); } );

class links_inline_descriptions_Widget extends WP_Widget {
	public function __construct( ) {
		parent::__construct(
			'links-inline-descriptions',
			_x( 'Links (Inline Descriptions)', 'widget name', 'links-inline-descriptions' ),
			array(
				'description' => __( 'Links with inline descriptions.', 'links-inline-descriptions' ),
			)
		);
	}

	public function widget( $args, $instance ) {
		extract( $args );
		$links = get_bookmarks( array( 'category' => $instance['category'] ) );

		if( is_array( $links ) ) {
			echo '<div class="links-improved">';
			echo $before_widget;
			$cat_info = get_term( $instance['category'], 'link_category' );
			echo $before_title . $cat_info->name . $after_title;
			echo '<ul>';
			foreach( $links as $link ) {
				echo '<li><a href="' . $link->link_url . '">';
				echo '<span class="name">' . $link->link_name . '</span><span class="description">' . $link->link_description . '</span>';
				echo '</a></li>';
			}
			echo '</ul>';
			echo $after_widget;
			echo '</div>';
		}
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['category'] = $new_instance['category'];
		return $instance;
	}

	public function form( $instance ) {
		$link_cats = get_terms( 'link_category' );
		?>
			<p>
				<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e( 'Link category:', 'links-inline-descriptions' ); ?></label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>">
				<option value=""><?php _ex( '-- All link categories --', 'widget option', 'links-inline-descriptions' ); ?></option>
				<?php
				foreach( $link_cats as $link_cat ) {
					echo '<option value="' . $link_cat->term_id . '" '	. selected( $instance['category'], $link_cat->term_id, false )	. '>' . $link_cat->name . '</option>';
				}
				?>
				</select>
			</p>
		<?php
	}
}
