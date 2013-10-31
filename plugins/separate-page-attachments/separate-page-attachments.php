<?php
/*
 *  Plugin Name: Separate Page Attachments
 *  Description: Use a separate directory for page attachments.
 *  Author: Pasi Lallinaho
 *  Version: 0.1
 *  Author URI: http://open.knome.fi/
 *  Plugin URI: http://wordpress.knome.fi/
 *
 *  License: GNU General Public License v2 or later
 *  License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

/*  On plugin activation, create options for default values if needed
 *
 */

register_activation_hook( __FILE__, 'SeparatePageAttachmentsActivate' );

function SeparatePageAttachmentsActivate( ) {
	// FIXME: Document
	add_option( 'page_attachments_upload_dir', 'pages' );
}

/*  Add filter to uploads
 *
 */

add_filter( 'upload_dir', 'SeparatePageAttachmentsUploadDir' );

function SeparatePageAttachmentsUploadDir( $pathdata ) {
	global $post;

	$parent = get_post( $post->post_parent );

	if( $parent->post_type = 'page' ) {
		$pathdata['subdir'] = "pages";
		$pathdata['path'] = $pathdata['basedir'] . '/' . $pathdata['subdir'];
		$pathdata['url'] = $pathdata['baseurl'] . '/' . $pathdata['subdir'];
	}

	return $pathdata;
}

?>
