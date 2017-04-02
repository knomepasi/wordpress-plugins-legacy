<?php
/*  Plugin Name: Article Series
 *  Description: Organize your articles in article series and promote the created series with a widget.
 *  Author: Pasi Lallinaho
 *  Version: 1.0.3
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

add_action( 'plugins_loaded', 'article_series_init_i18n' );

function article_series_init_i18n( ) {
	load_plugin_textdomain( 'article-series', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

/*  
 *  Flush rewrite rules on activation and deactivation to make sure all permalinks work
 *
 */

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
register_activation_hook( __FILE__, 'article_series_flush_rewrites' );

function article_series_flush_rewrites( ) {
	article_series_init( );
	flush_rewrite_rules( );
}

/*  
 *  Add the 'serie' taxonomy
 *
 */

add_action( 'init', 'article_series_init' );

function article_series_init( ) {
	$labels = array(
		'name' => _x( 'Article series', 'Taxonomy name', 'article-series' ),
		'singular_name' => _x( 'Article series', 'Taxonomy name (singular)', 'article-series' ),
		'search_items' => __( 'Search Series', 'article-series' ),
		'all_items' => __( 'All Series', 'article-series' ),
		'parent_item' => __( 'Parent Series', 'article-series' ),
		'parent_item_colon' => __( 'Parent Series:', 'article-series' ),
		'edit_item' => __( 'Edit Series', 'article-series' ),
		'update_item' => __( 'Update Series', 'article-series' ),
		'add_new_item' => __( 'Add New Series', 'article-series' ),
		'new_item_name' => __( 'New Series name', 'article-series' ),
//		'popular_items' => __( 'Popular Series', 'article-series' ),
		'separate_items_with_commas' => __( 'Separate series by commas', 'article-series' ),
		'add_or_remove_items' => __( 'Add or remove series', 'article-series' ),
		'choose_from_most_used' => __( 'Choose from the most used series', 'article-series' ),
		'not_found' => __( 'No Series found', 'article-series' ),
		'not_found_in_trash' => __( 'No Series found in trash', 'article-series' ),
		'menu_name' => __( 'Series', 'article-series' )
	);

	register_taxonomy( 'serie', array( 'post' ), array(
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'serie' )
	) );
}

/*
 *  Add a hook for the_content to promote the article series with each article included in one.
 *
 */

add_filter( 'the_content', 'article_series_the_content', 100 );

function article_series_the_content( $content ) {
	$series = get_the_term_list( get_the_ID( ), 'serie', null, ',', null );
	if( strlen( $series ) > 0 ) {
		$out = '<p class="post-serie">' . sprintf( __( 'This article is part of the article series %1$s.', 'article-series' ), $series ) . '</p>';
	}

	return $content . $out;
}

/*
 *  Add a column for series to the posts listing
 *
 */

add_filter( 'manage_edit-post_columns', 'article_series_edit_post_columns' );

function article_series_edit_post_columns( $columns ) {
	foreach( $columns as $key => $value ) {
		// We want to add our columns just before the "comments" column
		if( $key == "comments" ) {
			$new_columns['article_serie'] = __( 'Serie', 'article-series' );
		}

		$new_columns[$key] = $value;
	}

	return $new_columns;
}

add_action( 'manage_posts_custom_column', 'article_series_column_show' );

function article_series_column_show( $column ) {
	global $post;

	switch( $column ) {
		case 'article_serie':
			$terms = get_the_term_list( $post->ID, 'serie', null, ",", null );
			if( is_string( $terms ) ) {
				echo $terms;
			} else {
				echo '—';
			}
			break;
	}
}

/*
 *  Include some CSS for admin
 *
 */

add_action( 'admin_head', 'article_series_admin_head' );

function article_series_admin_head( ) {
	echo '<style> .column-article_serie { width: 20%; } </style>';
}

/*
 *  Register a widget to promote article series
 *
 */

add_action( 'widgets_init', function( ) { register_widget( 'article_series_Widget' ); } );

class article_series_Widget extends WP_Widget {
	public function __construct( ) {
		parent::__construct(
			'article_series',
			_x( 'Article series', 'widget name', 'article-series' ),
			array(
				'description' => __( 'Promote article series.', 'article-series' ),
			)
		);
	}

	public function widget( $args, $instance ) {
		extract( $args );

		echo '<div class="article-series article-series-promotion">';
		echo $before_widget;

		if( $instance['serie'] > 0 ) {
			$our_serie = get_term( $instance['serie'], 'serie' );
		} else {
			$all_series = get_terms( 'serie', array( 'hide_empty' => true ) );
			$random_id = array_rand( $all_series, 1 );
			$our_serie = $all_series[$random_id];
		}

		echo $before_title . $our_serie->name . $after_title;
		echo wpautop( $our_serie->description );
		echo '<p class="more more-link"><a href="' . home_url( '/serie/' . $our_serie->slug ) . '/">' . __( 'Read the article series', 'article-series' ) . ' &raquo;</a></p>';

		echo $after_widget;
		echo '</div>';
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['serie'] = strip_tags( $new_instance['serie'] );

		return $instance;
	}

	public function form( $instance ) {
		$serie = esc_attr( $instance['serie'] );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'serie' ); ?>"><?php _e( 'Serie', 'article-series' ); ?><br />
				<select class="widefat" id="<?php echo $this->get_field_id( 'serie' ); ?>" name="<?php echo $this->get_field_name( 'serie' ); ?>">
					<?php
						$series = get_terms( 'serie', array( 'hide_empty' => true ) );

						$random = new stdClass( );
						$random->name = __( '-- Random series --', 'article-series' );
						$random->term_id = 0;

						array_unshift( $series, $random );

						foreach( $series as $ser ) {
							echo '<option value="' . $ser->term_id . '" ' . selected( $ser->term_id, $serie, false ) . '>' . $ser->name . '</option>';
						}
					?>
				</select>
			</label>
		</p>
		<?php 
	}
}

?>
