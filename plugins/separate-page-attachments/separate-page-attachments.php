<?php
/*
 *  Plugin Name: Separate Page Attachments
 *  Description: Use a separate directory for page attachments.
 *  Author: Pasi Lallinaho
 *  Version: 0.1
 *  Author URI: http://open.knome.fi/
 *  Plugin URI: https://github.com/knomepasi/WordPress-plugins/
 *
 */

add_filter( 'upload_dir', 'page_upload_dir' );

function page_upload_dir( $pathdata ) {
	global $post;

	$parent = get_post( $post->post_parent );

	if( $parent->post_type = 'page' ) {
		$pathdata['subdir'] = "/pages";
		$pathdata['path'] = $pathdata['basedir'] . $pathdata['subdir'];
		$pathdata['url'] = $pathdata['baseurl'] . $pathdata['subdir'];
	}

	return $pathdata;
}

?>
