<?php

/*  Helper function to print <option>-list of all the showtypes
 *
 */

function get_showtype_options( $current ) {
	$showtypes = apply_filters( "project_catalog_showtype", $show );

	foreach( $showtypes as $type ) {
		if( $type['callback'] == $current ) { $selected = 'selected="selected"'; } else { unset( $selected ); }
		$opts .= '<option value="' . $type['callback'] . '" ' . $selected . '>' . $type['name'] . '</option>\n';
	}

	return $opts;
}

/*  Define default showtypes.
 *
 *  If you want to define more showtypes, you should first add a filter:
 *    add_filter( 'pc_showtypes', 'your_function_name' );
 *
 *  The function should take two parameters, $show array, append data into it:
 *     $show['your_show_type'] = array( "name" => "Your show type name",
 *     "callback" => "your_callback_function" );
 *  The other parameter should be the category data from database.
 *
 *  The callback function should take one parameter, $data array, which includes
 *     all the projects in the current category. The callback function should
 *     return a string with the markup.
 *
 */

/*  Showtype: Simple list
 *
 */

add_filter( 'project_catalog_showtype', 'showtype_list_define' );

function showtype_list_define( $showtypes ) {
	$type = array(
		"name" => _x( "Simple list", "showtype", "projects-catalog" ),
		"callback" => "showtype_list"
	);

	$showtypes[] = $type;
	return $showtypes;
}

function showtype_list( $data, $title ) {
	$out = '<div class="showtype-list">';
	$out .= "<h5>" . $title . "</h5>";
	$out .= "<ul>";
	foreach( $data as $project ) {
		$out .= '<li><a href="' . $project->permalink . '">' . $project->post_title . '</a></li>';
	}
	$out .= "</ul>";
	$out .= "</div>";

	return $out;
}

/*  Showtype: Thumbs
 *
 */

add_filter( 'project_catalog_showtype', 'showtype_thumbs_define' );

function showtype_thumbs_define( $showtypes ) {
	$type = array(
		"name" => _x( "Thumbs", "showtype", "projects-catalog" ),
		"callback" => "showtype_thumbs"
	);

	$showtypes[] = $type;
	return $showtypes;
}

function showtype_thumbs( $data, $title ) {
	$out = "<div class=\"showtype-thumbs showtype-image group\">";
	$out .= "<h5>" . $title . "</h2>";
	$out .= "<ul>";

	foreach( $data as $project ) {
		$img = wp_get_attachment_image_src( get_post_thumbnail_id( $project->ID ), 'thumbnail' );
		$out .= '<li class="group">';
		$out .= '<a href="' . $project->permalink . '">';
		if( $img[0] ) { $out .= '<img src="' . $img[0] . '" />'; }
		$out .= '<strong>' . $project->post_title . '</strong>';
		$out .= '</a>';
		$out .= '</li>';
	}
	$out .= "</ul>";
	$out .= "</div>";

	return $out;
}

/*  Showtype: Screenshot with project name and description
 *
 */

add_filter( 'project_catalog_showtype', 'showtype_shots_define' );

function showtype_shots_define( $showtypes ) {
	$type = array(
		"name" => _x( "Screenshot with project name and description", "showtype", "projects-catalog" ),
		"callback" => "showtype_shots"
	);

	$showtypes[] = $type;
	return $showtypes;
}

function showtype_shots( $data, $title ) {
	$out = '<div class="showtype-shots showtype-image group" style="margin-bottom: 2em;">';
	$out .= '<h2>' . $title . '</h2>';
	$out .= '<ul>';

	foreach( $data as $project ) {
		$img = wp_get_attachment_image_src( get_post_thumbnail_id( $project->ID ), 'medium' );
		$custom = get_post_custom( $project->ID );		

		$out .= '<li class="project-shot-wrap" style="background-image: url(' . $img[0] . ');">';
		$out .= '<a class="project-shot-link" href="' . $project->permalink . '">';
		$out .= '<span class="project-shot-name"><span>' . $project->post_title . '</span></span>';
		$out .= '<span class="project-shot-description"><span>' . $custom['Description'][0] . '</span></span>';
		$out .= '</a>';
		$out .= '</li>';
	}

	$out .= '</ul>';
	$out .= '</div>';

	return $out;
}

/*  Showtype: Small mosaic
 *
 */

add_filter( 'project_catalog_showtype', 'showtype_mosaic_define' );
add_image_size( 'small-mosaic', 120, 9999 );

function showtype_mosaic_define( $showtypes ) {
	$type = array(
		"name" => _x( "Small mosaic", "showtype", "projects-catalog" ),
		"callback" => "showtype_mosaic"
	);

	$showtypes[] = $type;
	return $showtypes;
}

function showtype_mosaic( $data, $title ) {
	$out = '<div class="showtype-mosaic showtype-image group" style="margin-bottom: 2em;">';
//	$out .= '<h2>' . $title . '</h2>';
	$out .= '<ul>';

	foreach( $data as $project ) {
		$img = wp_get_attachment_image_src( get_post_thumbnail_id( $project->ID ), 'small-mosaic' );

		$out .= '<li class="project-shot-wrap" style="background-image: url(' . $img[0] . ');">';
		$out .= '<a class="project-shot-link" href="' . $project->permalink . '">';
		$out .= '<span class="project-shot-name"><span>' . $project->post_title . '</span></span>';
		$out .= '</a>';
		$out .= '</li>';
	}

	$out .= '</ul>';
	$out .= '</div>';

	return $out;
}


?>
