<?
/*  Plugin Name: Projects Catalog
 *  Description: Organize and show your projects.
 *  Author: Pasi Lallinaho
 *  Version: 2.1
 *  Author URI: http://open.knome.fi/
 *  Plugin URI: http://wordpress.knome.fi/
 *
 */

/*  On plugin activation, create databases and options for default values if needed
 *
 */

register_activation_hook( __FILE__, 'ProjectsCatalogActivate' );

function ProjectsCatalogActivate( ) {
	global $wpdb;
	$wpdb->project_groupmeta = $wpdb->prefix . "project_groupmeta";

	if( !empty( $wpdb->charset ) ) { $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset"; }
	if( !empty( $wpdb->collate ) ) { $charset_collate .= " COLLATE $wpdb->collate"; }

	if( $wpdb->query( "SHOW tables LIKE '" . $wpdb->project_groupmeta . "'" ) == 0 ) {
		// Table 'project_group' does not exist, create it
		$wp_query = "CREATE TABLE " . $wpdb->project_groupmeta . " (
				meta_id bigint(20) unsigned NOT NULL auto_increment,
				project_group_id bigint(20) unsigned NOT NULL default '0',
				meta_key varchar(255) default NULL,
				meta_value longtext,
				PRIMARY KEY (meta_id),
				KEY post_id (project_group_id),
				KEY meta_key (meta_key)
			)" . $charset_collate;

		$wpdb->query( $wp_query );
	}
}

/*  Init plugin
 *
 */

add_action( 'plugins_loaded', 'ProjectsCatalogInit' );

function ProjectsCatalogInit( ) {
	/* Load text domain for i18n */
	load_plugin_textdomain( 'projects-catalog', false, dirname( plugin_basename( FILE ) ) . '/languages/' );

	/* Init database */
	global $wpdb;
	$wpdb->project_groupmeta = $wpdb->prefix . "project_groupmeta";
}

/*  Register new post type 'project'
 *
 */

add_action( 'init', 'project_posttype_init' );

function project_posttype_init( ) {
	$labels = array(
		'name' => _x( 'Projects', 'Post type', 'projects-catalog' ),
		'singular_name' => _x( 'Project', 'Post type (singular)', 'projects-catalog' ),
		'add_new' => _x( 'Add New', 'project', 'projects-catalog' ),
		'add_new_item' => __( 'Add New Project', 'projects-catalog' ),
		'edit_item' => __( 'Edit Project', 'projects-catalog' ),
		'new_item' => __( 'New Project', 'projects-catalog' ),
		'view_item' => __( 'View Project', 'projects-catalog' ),
		'search_items' => __( 'Search Projects', 'projects-catalog' ),
		'not_found' => __( 'No projects found', 'projects-catalog' ),
		'not_found_in_trash' => __( 'No projects found in Trash', 'projects-catalog' ),
		'parent_item_colon' => __( 'Main Project', 'projects-catalog' ),
		'menu_name' => __( 'Projects', 'projects-catalog' )
	);
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'exclude_from_search' => false,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_position' => 4,
		'menu_icon' => null, // FIXME: add a custom icon
		'capability_type' => 'page',
		'hierarchical' => true,
		'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'comments',	'revisions' ),
		'taxonomies' => array( 'project_group' )
	);

	register_post_type( 'project', $args );
}

/*  Add taxonomies for projects
 *
 */

add_action( 'init', 'project_taxonomy_init', 0 );

function project_taxonomy_init( ) {
	global $wpdb;

	$labels = array(
		'name' => _x( 'Project Group', 'Taxonomy name', 'projects-catalog' ),
		'singular_name' => _x( 'Project Group', 'Taxonomy name (singular)', 'projects-catalog' ),
		'search_items' => __( 'Search Project Groups', 'projects-catalog' ),
		'all_items' => __( 'All Project Groups', 'projects-catalog' ),
		'parent_item' => '',
		'parent_item_colon' => '',
		'edit_item' => __( 'Edit Project Group', 'projects-catalog' ),
		'update_item' => __( 'Update Project Group', 'projects-catalog' ),
		'add_new_item' => __( 'Add New Project Group', 'projects-catalog' ),
		'new_item_name' => __( 'New Project Group name', 'projects-catalog' ),
		'menu_name' => __( 'Groups', 'projects-catalog' )
	);

	register_taxonomy( 'project_group', array( 'project' ), array(
		'hierarchical' => false,
		'labels' => $labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => false
	) );
}

/*  Add new fields for the 'Project Group' taxonomy
 *
 */

add_action( 'project_group_edit_form_fields', 'project_group_fields', 10, 2 );

function project_group_fields( $tag, $taxonomy ) {
	$project_group_showtype = get_metadata( $tag->taxonomy, $tag->term_id, 'project_group_showtype', true );
	$project_group_menu_order = get_metadata( $tag->taxonomy, $tag->term_id, 'project_group_menu_order', true );

	if( !$project_group_menu_order ) { $project_group_menu_order = 0; }
	?>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="project_group_showtype"><?php _e( 'Showtype', 'projects-catalog' ); ?></label></th>
		<td>
			<select id="project_group_showtype" name="project_group_showtype">
				<?php echo get_showtype_options( $project_group_showtype ); ?>
			</select><br />
			<p class="description"><?php _e( 'This selection defines how the project group is presented by default.', 'projects-catalog' ); ?></p>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="project_group_menu_order"><?php _e( 'Order', 'projects-catalog' ); ?></label></th>
		<td>
			<input type="text" id="project_group_menu_order" name="project_group_menu_order" value="<?php echo $project_group_menu_order; ?>" /><br />
			<p class="description"><?php _e( 'The sort order. Smaller integers go bottom.', 'projects-catalog' ); ?></p>
		</td>
	</tr>
	<?php
}

add_action( 'edited_project_group', 'project_group_fields_save', 10, 2 );

function project_group_fields_save( $term_id, $ttid ) {
	if( !$term_id ) { return; }

	if( isset( $_POST['project_group_showtype'] ) ) {
		update_metadata( $_POST['taxonomy'], $term_id, 'project_group_showtype', $_POST['project_group_showtype'] );
	}

	if( isset( $_POST['project_group_menu_order'] ) ) {
		update_metadata( $_POST['taxonomy'], $term_id, 'project_group_menu_order', $_POST['project_group_menu_order'] );
	}
}

/*  Add filter to show messages on project updates
 *
 */

add_filter( 'post_updated_messages', 'project_updated_messages' );

function project_updated_messages( $messages ) {
	global $post, $post_ID;

	$messages['project'] = array(
		0 => '',
		1 => sprintf( __( 'Project updated. <a href="%s">View project</a>', 'projects-catalog' ), esc_url( get_permalink( $post_ID ) ) ),
		2 => __( 'Custom field updated.', 'projects-catalog' ),
		3 => __( 'Custom field deleted.', 'projects-catalog' ),
		4 => __( 'Project updated.', 'projects-catalog' ),
		// Translators: %s: Date and time of the revision
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Project restored to revision from %s', 'projects-catalog' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Project published. <a href="%s">View project</a>', 'projects-catalog' ), esc_url( get_permalink( $post_ID ) ) ),
		7 => __( 'Project saved.', 'projects-catalog' ),
		8 => sprintf( __( 'Project submitted. <a target="_blank" href="%s">Preview project</a>', 'projects-catalog' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		9 => sprintf( __( 'Project scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview project</a>', 'projects-catalog' ),
			// Translators: Publish box date format, see http://php.net/date/
			date_i18n( __( 'M j, Y @ G:i', 'projects-catalog' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( 'Project draft updated. <a target="_blank" href="%s">Preview project</a>', 'projects-catalog' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) )
	);

	return $messages;
}

/*  Make sure permalinks work
 *
 */

function project_rewrite_flush( ) {
	project_posttype_init( );
	flush_rewrite_rules( );
}

register_activation_hook( __FILE__, 'project_rewrite_flush' );

/*  Add more columns to Projects listing
 *
 */

add_filter( 'manage_edit-project_columns', 'project_columns_register' );

function project_columns_register( $columns ) {
	foreach( $columns as $key => $value ) {
		// We want to add our columns just before the "comments" column
		if( $key == "comments" ) {
			$new_columns['project_group'] = __( 'Project Group', 'projects-catalog' );
		}

		$new_columns[$key] = $value;
	}

	return $new_columns;
}

add_action( 'manage_pages_custom_column', 'project_columns_show' );

function project_columns_show( $column ) {
	global $post;

	switch( $column ) {
		case 'project_group':
			$terms = get_the_term_list( $post->ID, 'project_group', null, ",", null );
			if( is_string( $terms ) ) {
				echo $terms;
			} else {
				_e( "Not in any group.", "projects-catalog" );
			}
			break;
	}
}

/*  Include default stylesheets
 *
 */

add_action( 'wp_enqueue_scripts', 'ProjectsCatalogScripts' );

function ProjectsCatalogScripts( ) {
	wp_register_style( 'projects-catalog-defaults', plugins_url( 'defaults.css', __FILE__ ) );
	wp_enqueue_style( 'projects-catalog-defaults' );
}

/*  Load the file with default showtypes
 *
 */

include "showtypes.php";

/*  Add the widget and the shortcode for end-user usage
 *
 */

include "widget_shortcode.php";

?>
