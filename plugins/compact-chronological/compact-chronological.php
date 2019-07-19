<?php
/*
 *  Plugin Name: Compact & Chronological
 *  Description: Show your monthly archive links in a compact view.
 *  Author: Pasi Lallinaho
 *  Version: 1.3
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

add_action( 'plugins_loaded', 'compact_chronological_init' );

function compact_chronological_init( ) {
	load_plugin_textdomain( 'compact-chronological', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

/*
 *  Include stylesheet for default styling
 *
 */

add_action( 'wp_enqueue_scripts', 'compact_chronological_scripts' );

function compact_chronological_scripts( ) {
	wp_register_style( 'compact-chronological-defaults', plugins_url( 'defaults.css', __FILE__ ) );
	wp_enqueue_style( 'compact-chronological-defaults' );
}


/*
 *  Register a widget to show the compact monthly view
 *
 */

add_action( 'widgets_init', function( ) { register_widget( 'compact_chronological_Widget' ); } );

class compact_chronological_Widget extends WP_Widget {
	public function __construct( ) {
		parent::__construct(
			'compact-chronological',
			_x( 'Compact & Chronological', 'widget name', 'compact-chronological' ),
			array(
				'description' => __( 'Monthly archive links in a compact view.', 'compact-chronological' ),
			)
		);
	}

	/** @see WP_Widget::widget */
	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if( $title ) { echo $before_title . $title . $after_title; }

		global $wpdb;

		$query = 'SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts FROM ' . $wpdb->posts . ' WHERE post_type = "post" AND post_status = "publish" GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY YEAR(post_date) DESC, MONTH(post_date) ASC';
		$archives = $wpdb->get_results( $query );

		if( is_array( $archives ) ) {
			echo '<div class="compact-chrono">';

			foreach( $archives as $current ) {
				$arch[$current->year][$current->month] = $current->posts;
				$ycounts[$current->year] += $current->posts;
			}

			$last_year = $archives['0']->year;
			$first_year = end( $archives )->year;

			for( $y = $last_year; $y >= $first_year; $y-- ) {
				echo '<div class="year">';
				echo '<a class="year_name" href="' . get_year_link( $y ) . '" title="' . $y . ": " . sprintf( _n( '%d topic', '%d topics', $ycounts[$y], 'compact-chronological' ), $ycounts[$y] ) . '">';
					echo '<span class="name">' . $y . '</span>';
				echo '</a>';

				echo '<ul>';
				for( $m = 1; $m <= 12; $m++ ) {
					if( $m > date( 'n' ) && $y >= date( 'Y' ) ) { $class = 'month future'; }
					elseif( $m == date( 'n' ) && $y == date( 'Y' ) ) { $class = 'month now'; }
					else { $class = 'month past'; }

					echo '<li class="' . $class . '">';

					$month_name = ucfirst( strftime( "%B %Y", mktime( 0, 0, 0, $m, 10, $y ) ) );
					$month_abbr = ucfirst( strftime( "%b", mktime( 0, 0, 0, $m, 10, $y ) ) );

					if( $arch[$y][$m] < 1 ) {
						print substr( $month_abbr, 0, 3 );
					} else {
						echo '<a href="' . get_month_link( $y, $m ) . '" title="' . $month_name . ": " . sprintf( _n( '%d topic', '%d topics', $arch[$y][$m], 'compact-chronological' ), $arch[$y][$m] ) . '">';
						echo '<span class="name">' . substr( $month_abbr, 0, 3 ) . '</span>';
						if( $instance['article_counts'] == 1 ) {
							echo '<span class="count">' . $arch[$y][$m] . '</span>';
						}
						echo '</a>';
					}
					echo '</li>';
				}
				echo '</ul>';
				echo '</div>';
			}
			echo '</div>';
		}
		/* */
		echo $after_widget;
	}

	/** @see WP_Widget::update */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['split_rows'] = $new_instance['split_rows'];

		return $instance;
	}

	/** @see WP_Widget::form */
	public function form( $instance ) {
		$title = esc_attr( $instance['title'] );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'compact-chronological' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
			</label>
		</p>
		<?php 
	}
}

// include 'cc-gutenberg.php';

?>
