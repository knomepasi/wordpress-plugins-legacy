<?
/*  Plugin Name: Article Series
 *  Description: Adds a new taxonomy "series" for posts.
 *  Author: Pasi Lallinaho
 *  Version: 1.0
 *  Author URI: http://open.knome.fi/
 *  Plugin URI: https://github.com/knomepasi/WordPress-plugins
 *
 */

/*
 *  Load textdomain for translations
 *
 */

load_plugin_textdomain( 'article-series', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

/*  Add taxonomies for projects
 *
 */

add_action( 'init', 'ArticleSeriesInit' );

function ArticleSeriesInit( ) {
	$labels = array(
		'name' => _x( 'Article series', 'Taxonomy name', 'article-series' ),
		'singular_name' => _x( 'Article serie', 'Taxonomy name (singular)', 'article-series' ),
		'search_items' => __( 'Search Series', 'article-series' ),
		'all_items' => __( 'All Series', 'article-series' ),
		'parent_item' => __( 'Parent Serie', 'article-series' ),
		'parent_item_colon' => __( 'Parent Serie:', 'article-series' ),
		'edit_item' => __( 'Edit Serie', 'article-series' ),
		'update_item' => __( 'Update Serie', 'article-series' ),
		'add_new_item' => __( 'Add New Serie', 'article-series' ),
		'new_item_name' => __( 'New Serie name', 'article-series' ),
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
 *  Add a new option column to the posts listing
 *
 */

add_filter( 'manage_edit-post_columns', 'ArticleSeriesColumnRegister' );

function ArticleSeriesColumnRegister( $columns ) {
	foreach( $columns as $key => $value ) {
		// We want to add our columns just before the "comments" column
		if( $key == "comments" ) {
			$new_columns['article_serie'] = __( 'Serie', 'article-series' );
		}

		$new_columns[$key] = $value;
	}

	return $new_columns;
}

add_action( 'manage_posts_custom_column', 'ArticleSeriesColumnShow' );

function ArticleSeriesColumnShow( $column ) {
	global $post;

	switch( $column ) {
		case 'article_serie':
			$terms = get_the_term_list( $post->ID, 'serie', null, ",", null );
			if( is_string( $terms ) ) {
				echo $terms;
			} else {
//				_e( "Not in any serie.", "article-series" );
				echo "—";
			}
			break;
	}
}

/*
 *  Include some CSS for admin
 *
 */

add_action( 'admin_head', 'ArticleSeriesAdminHead' );

function ArticleSeriesAdminHead( ) {
	print "<style> .column-article_serie { width: 20%; } </style>";
}

/*  Make sure permalinks work
 *
 */

function ArticleSeriesRewriteFlush( ) {
	ArticleSeriesInit( );
	flush_rewrite_rules( );
}

register_activation_hook( __FILE__, 'ArticleSeriesRewriteFlush' );

/*
 *  Add a widget to promote article series
 *
 */

add_action( 'widgets_init', create_function( '', 'return register_widget("ArticleSeriesWidget");' ) );

class ArticleSeriesWidget extends WP_Widget {
	/** constructor */
	function ArticleSeriesWidget( ) {
		parent::WP_Widget(
			'article_series',
			_x( 'Article series', 'widget name', 'article-series' ),
			array(
				'description' => __( 'Promote article series.', 'article-series' ),
			)
		);
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );

		echo '<div class="article-series-promotion">';
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
		echo '<p class="more-link"><a href="' . home_url( '/serie/' . $our_serie->slug ) . '/">' . __( 'Read the article series', 'article-series' ) . ' &raquo;</a></p>';

		echo $after_widget;
		echo '</div>';
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['serie'] = strip_tags( $new_instance['serie'] );

		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		$serie = esc_attr( $instance['serie'] );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'serie' ); ?>"><?php _e( 'Serie', 'article-series' ); ?><br />
				<select class="widefat" id="<?php echo $this->get_field_id( 'serie' ); ?>" name="<?php echo $this->get_field_name( 'serie' ); ?>">
					<?php
						$series = get_terms( 'serie', array( 'hide_empty' => true ) );

						$random = new stdClass( );
						$random->name = __( '-- Random serie --', 'article-series' );
						$random->term_id = 0;

						array_unshift( $series, $random );

						foreach( $series as $ser ) {
							if( $ser->term_id == $serie ) { $is_selected = ' selected="selected " '; } else { unset( $is_selected ); }
							print '<option value="' . $ser->term_id . '"' . $is_selected . '>' . $ser->name . '</option>';
						}
					?>
				</select>
			</label>
		</p>
		<?php 
	}

}

?>
