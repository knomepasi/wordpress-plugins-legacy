<?php
/*
 *  Plugin Name: Interactive Images
 *  Description: Create interactive images by adding notes on top of images.
 *  Author: Pasi Lallinaho
 *  Version: 1.4
 *  Author URI: http://open.knome.fi/
 *  Plugin URI: http://wordpress.knome.fi/
 *
 *  License: GNU General Public License v2 or later
 *  License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 *  Additional optimizations provided by Alexander Blomen (ablomen).
 *
 */

/*  On plugin activation, create databases and options for default values if needed
 *
 */

register_activation_hook( __FILE__, 'InteractiveImagesActivate' );

function InteractiveImagesActivate( ) {
	global $wpdb;
	$wpdb->interactive_images = $wpdb->prefix . "interactive_images";
	$wpdb->interactive_captions = $wpdb->prefix . "interactive_captions";

	if( !empty( $wpdb->charset ) ) { $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset"; }
	if( !empty( $wpdb->collate ) ) { $charset_collate .= " COLLATE $wpdb->collate"; }

	if( $wpdb->query( "SHOW tables LIKE '" . $wpdb->interactive_images . "'" ) == 0 ) {
		// Table 'interactive_images' does not exist, create it
		$wp_query = "CREATE TABLE " . $wpdb->interactive_images . " (
				`image_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
				`image_file` TEXT NOT NULL,
				`image_title` TEXT NOT NULL,
				`image_box_width` INT NOT NULL,
				`image_width` INT NOT NULL,
				`image_height` INT NOT NULL
			)" . $charset_collate;

		$wpdb->query( $wp_query );
	}

	if( $wpdb->query( "SHOW tables LIKE '" . $wpdb->interactive_captions . "'" ) == 0 ) {
		// Table 'interactive_captions' does not exist, create it
		$wp_query = "CREATE TABLE " . $wpdb->interactive_captions . " (
				`caption_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
				`caption_parent` INT NOT NULL,
				`caption_x` INT NOT NULL,
				`caption_y` INT NOT NULL,
				`caption_text` TEXT NOT NULL
			)" . $charset_collate;

		$wpdb->query( $wp_query );
	}

	/* add options, these are default values */
	add_option( 'interactive_images_default_box_width', 145 );
	add_option( 'interactive_images_upload_dir', 'iimages' );
}

/*  Init plugin
 *
 */

add_action( 'plugins_loaded', 'InteractiveImagesInit' );

function InteractiveImagesInit( ) {
	/* Load text domain for i18n */
	load_plugin_textdomain( 'interactive-images', false, dirname( plugin_basename( FILE ) ) . '/languages/' );

	/* Init database */
	global $wpdb;
	$wpdb->interactive_images = $wpdb->prefix . "interactive_images";
	$wpdb->interactive_captions = $wpdb->prefix . "interactive_captions";
}

/*  Include some CSS
 *
 */

add_action( 'wp_head', 'InteractiveImagesHead' );
add_action( 'admin_head', 'InteractiveImagesHead' );

function InteractiveImagesHead( ) {
	print "<!--[if IE]><style type=\"text/css\">";
	print ".iimage_caption span.c_main { filter: alpha(opacity = 0); zoom: 1; }";
	print ".iimage_caption:hover span.c_main { filter: alpha(opacity = 100); }";
	print "</style><![endif]-->\n";

	print "<!--[if lt IE 8]><style type=\"text/css\">";
	print ".iimage_caption span.c_main { background-color: #222; color: #fff; }";
	print "</style><![endif]-->\n";
}

/*  Include scripts and stylesheets
 *
 */

add_action( 'wp_enqueue_scripts', 'InteractiveImagesScripts' );
add_action( 'admin_enqueue_scripts', 'InteractiveImagesScripts' );

function InteractiveImagesScripts( ) {
	wp_enqueue_script( 'jquery' );

	wp_register_script( 'interactive-images', plugins_url( 'images.js', __FILE__ ), array( 'jquery', '1.3' );
	wp_enqueue_script( 'interactive-images' );

	if( is_admin( ) ) {
		wp_register_script( 'interactive-images-admin', plugins_url( 'images-admin.js', __FILE__ ), array( 'jquery' ), '1.3' );
		wp_enqueue_script( 'interactive-images-admin' );
	}

	wp_register_style( 'interactive-images-default', plugins_url( 'defaults.css', __FILE__ ) );
	wp_enqueue_style( 'interactive-images-default' );
}

/*  Add shortcode
 *
 */

add_shortcode( 'iimage', 'InteractiveImagesShortCode' );

function InteractiveImagesShortCode( $atts, $content, $code ) {
	extract( shortcode_atts( array(
		'id' => '',
		'box' => ''
	), $atts ) );

	$uppath = wp_upload_dir( );

	global $wpdb;
	$image = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->interactive_images} WHERE image_id = %s", $id ), OBJECT );

	if( $box ) { $image->image_box_width = $box; }

	if( is_object( $image ) ) {
		$out .= InteractiveImagesFooter( $image );
		$out .= '<div class="iimage_wrap" id="iimage_' . $image->image_id .'" width="' . $image->image_width . '" height="' . $image->image_height . '">';
		$out .= '<img src="' . $uppath['baseurl'] . "/" . get_option( 'interactive_images_upload_dir' ) . "/" . $image->image_file . '" alt="" />';
		$out .= '</div>';

		return $out;
	} else {
		$out .= '[iimage id=' . $id . ']';
		return $out;
	}
}

/*  Add a helper function
 *
 */

function InteractiveImagesFooter( $image ) {
	global $wpdb;

	$out = '<script type="text/javascript">';
	$out .= 'jQuery( window ).load( function( ) {';

	/* captions */
	$out .= 'var cap_' . $image->image_id . ' = [' . "\n";
	$captions = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->interactive_captions} WHERE caption_parent = %d", $image->image_id ), ARRAY_A );
	foreach( $captions as $caption ) {
		$out .= '{ "parent": "iimage_' . $image->image_id . '", "pos_x": ' . $caption['caption_x'] . ', "pos_y": ' . $caption['caption_y'] . ', "text": "' . $caption['caption_text'] . '", "id": ' . $caption['caption_id'] . ' },' . "\n";
	}
	$out .= ']; ';

	/* image options */
	$option = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->interactive_images} WHERE image_id = %d", $image->image_id ), ARRAY_A );
	if( $image->image_box_width ) { $option['image_box_width'] = $image->image_box_width; }
	$out .= 'var opts_' . $image->image_id . ' = { "box_width": ' . $option['image_box_width'] . ' };';

	$out .= 'findCaptions( cap_' . $image->image_id . ', opts_' . $image->image_id . ' ); } );';
	$out .= '</script>';

	return $out;
}

/*  Create admin menu
 *
 */

add_action( 'admin_menu', 'InteractiveImagesMenus' );

function InteractiveImagesMenus( ) {
	$iimage_admin_main = add_menu_page( __( 'Interactive Images Preferences', 'interactive-images' ), 'Interactive Images', 'upload_files', 'interactive_images', 'InteractiveImagesMenuImages', null, 50 );
	$iimage_admin_sub_images = add_submenu_page( 'interactive_images', __( 'Interactive Images', 'interactive-images' ), __( 'Images', 'interactive-images' ), 'upload_files', 'interactive_images', 'InteractiveImagesMenuImages' );
	$iimage_admin_sub_add = add_submenu_page( 'interactive_images', __( 'Add/Edit Interactive Image', 'interactive-images' ), __( 'Add/Edit', 'interactive-images' ), 'upload_files', 'interactive_form', 'InteractiveImagesMenuForm' );

	add_action( 'load-' . $iimage_admin_main, 'InteractiveImagesHelp' );
	add_action( 'load-' . $iimage_admin_sub_images, 'InteractiveImagesHelp' );
	add_action( 'load-' . $iimage_admin_sub_add, 'InteractiveImagesHelp' );
}

function InteractiveImagesMenuImages( ) {
	global $wpdb;

	print '<div class="wrap">';

	if( $_GET['mode'] == "del" && wp_verify_nonce( $_GET['_wpnonce'], 'interactive_image_del' ) ) {
		// user is deleting an image
		$db = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->interactive_images} WHERE image_id = %s", $_GET['icid'] ) );
		if( $db === false ) {
			print '<div id="message" class="error"><p>' . sprintf( __( '<strong>Error deleting image:</strong> %s', 'interactive-images' ), $wpdb->last_error ) . '</p></div>';
		} else {
			print '<div id="message" class="updated"><p>' . __( '<strong>Image deleted.</strong>', 'interactive-images' ) . '</p></div>';
		}
	}

	print '<h2>' . __( 'Interactive Images', 'interactive-images' ) . ' <a href="admin.php?page=interactive_form&mode=new" class="button add-new-h2">' . __( 'Add New', 'interactive-images' ) . '</a> </h2>';

	print '<table class="widefat">';
	print '<thead>';
	print '<tr><th style="width: 120px;"></th><th>' . __( 'Image', 'interactive-images' ) . '</th></tr>';
	print '</thead>';

	$images = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->interactive_images}" ), ARRAY_A );
	foreach( $images as $image ) {
		$del_nonce = wp_create_nonce( 'interactive_image_del' );

		print '<tr>';
		$uppath = wp_upload_dir( );
		print '<td><img src="' . $uppath['baseurl'] . '/' . get_option( 'interactive_images_upload_dir' ) . '/' . $image['image_file'] . '" alt="" width="120" /></td>';
		print '<td><strong>' . $image['image_title'] . '</strong><br />';
		print '<u>Interactive Image ID ' . $image['image_id'] . '</u><br />';
		$captions = $wpdb->get_row( $wpdb->prepare( "SELECT COUNT(*) AS count FROM {$wpdb->interactive_captions} WHERE caption_parent = %s", $image['image_id'] ), ARRAY_A );
		print ( $captions['count'] == 1 ? __( '1 caption', 'interactive-images' ) : ( $captions['count'] == 0 ? __( 'No captions', 'interactive-images' ) : sprintf( __( "%d captions", 'interactive-images' ), $captions['count'] ) ) );
			print '<div class="row-actions">';
			print '<a href="admin.php?page=interactive_form&mode=edit&icid=' . $image['image_id'] . '" title="' . __( 'Edit and preview', 'interactive-images' ) . '">' . __( 'Edit and preview', 'interactive-images' ) . '</a> | ';
			print '<span class="delete"><a onclick="return delInteractiveImage( \'' . $image['image_title'] . '\' );" href="admin.php?page=interactive_images&mode=del&icid=' . $image['image_id'] . '&_wpnonce=' . $del_nonce . '" title="' . __( 'Delete permanently', 'interactive-images' ) . '">' . __( 'Delete permanently', 'interactive-images' ) . '</a></span>';
			print '</div>';
		print '</td>';
		print '</tr>';
	}
	
	print '</table>';

	print '</div>';
}

function InteractiveImagesMenuForm( ) {
	print '<div class="wrap">';

	/* is the user uploading an image? */
	if( $_FILES['iimage_upload']['size'] > 0 && !$_POST['icid'] ) {
		$uppath = wp_upload_dir( );

		// check if the file exists!!
		$destination = $uppath['basedir'] . "/" . get_option( 'interactive_images_upload_dir' ) . "/" . $_FILES['iimage_upload']['name'];
		unset( $suffix );
		while( file_exists( $destination ) ) {
			$suffix++;

			$fn = $_FILES['iimage_upload']['name'];
			$filename = substr( $fn, 0, strrpos( $fn, "." ) ) . "_" . $suffix . substr( $fn, strrpos( $fn, "." ) );

			$destination = $uppath['basedir'] . "/" . get_option( 'interactive_images_upload_dir' ) . "/" . $filename;
		}
		if( !$filename ) { $filename = $_FILES['iimage_upload']['name']; }

		if( is_writable( $uppath['basedir'] . "/" . get_option( 'interactive_image_upload_dir' ) . "/" ) ) {
			if( move_uploaded_file( $_FILES['iimage_upload']['tmp_name'], $destination ) === false ) {
				print '<div id="message" class="error"><p>' . __( '<strong>Error with moving uploaded file.</strong>', 'interactive-images' ) . '</p></div>';
				$error_up = true;
			}

			$image_attr = getimagesize( $destination );

			// insert image data in to database
			global $wpdb;
			$wpdb->insert( $wpdb->interactive_images, array( "image_file" => $filename ), array( '%s' ) );
			$icid = $wpdb->insert_id;
			$wpdb->update( $wpdb->interactive_images, array( "image_title" => __( "Interactive Image", 'interactive-images' ) . " #" . $icid, "image_width" => $image_attr[0], "image_height" => $image_attr[1] ), array( "image_id" => $icid ), array( '%s', '%d', '%d' ), '%d' );
		} else {
			$error_up = '<div id="message" class="error"><p>' . sprintf( __( '<strong>Error:</strong> Check file permissions; "%s" is not writable.', 'interactive-images' ), $destination ) . '</p></div>';
			print $error_up;
		}
	} else {
		$icid = $_GET['icid'];
	}

	/* is the user saving? */
	if( $_POST['iimage_save'] ) {
		global $wpdb;
		unset( $error );

		/* save options */
		if( strlen( $_POST['opts']['image_title'] ) < 1 ) { $_POST['opts']['image_title'] = __( "Interactive Image", 'interactive-images' ) . " #" . $_POST['icid']; }
		if( $_POST['opts']['image_box_width'] < 1 ) { $_POST['opts']['image_box_width'] = get_option( 'interactive_images_default_box_width' ); }
		foreach( $_POST['opts'] as $k => $v ) { $_POST['opts'][$k] = esc_attr( stripslashes( $v ) ); }
		// $data (==$_POST['opts']) = (array) Data to update (in column => value pairs). Both $data columns and $data values should be "raw" (neither should be SQL escaped).
		$db = $wpdb->update( $wpdb->interactive_images, $_POST['opts'], array( "image_id" => $_POST['icid'] ), array( '%s', '%s' ), '%d' );
		if( $db === false ) { $error .= sprintf( __( 'Error updating options: %s', 'interactive-images' ), $wpdb->last_error ); print '<br />'; }

		/* update old captions */
		if( is_array( $_POST['caption'] ) ) {
			foreach( $_POST['caption'] as $id => $data ) {
				foreach( $data as $k => $v ) { $data[$k] = esc_attr( stripslashes( $v ) ); }
				// $data = (array) Data to update (in column => value pairs). Both $data columns and $data values should be "raw" (neither should be SQL escaped). 
				$db = $wpdb->update( $wpdb->interactive_captions, $data, array( 'caption_id' => $id ), array( '%d', '%d', '%s' ), '%d' );
				if( $db === false ) { $error .= sprintf( __( 'Error updating captions: %s', 'interactive-images' ), $wpdb->last_error ); print '<br />'; }
				$saved[] = $id;
			}
		}

		/* save new captions */
		if( is_array( $_POST['caption_new'] ) ) {
			foreach( $_POST['caption_new'] as $id => $data ) {
				foreach( $data as $k => $v ) { $data[$k] = esc_attr( stripslashes( $v ) ); }
				$data['caption_parent'] = $icid;
				// $data = (array) Data to update (in column => value pairs). Both $data columns and $data values should be "raw" (neither should be SQL escaped). 
				$db = $wpdb->insert( $wpdb->interactive_captions, $data, array( '%d', '%d', '%s' ) );
				if( $db === false ) { $error .= sprintf( __( 'Error inserting new captions: %s', 'interactive-images' ), $wpdb->last_error ); print '<br />'; }
				$saved[] = $wpdb->insert_id;
			}
		}

		/* delete all captions that were not there any more */
		if( is_array( $saved ) ) {
			foreach( $saved as $id ) { $where .= "AND caption_id != " . $wpdb->escape( $id ) . " "; }
			$db = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->interactive_captions} WHERE caption_parent = %d " . $where, $_POST['icid'] ) );
			if( $db === false ) { $error .= sprintf( __( 'Error cleaning up captions: %s', 'interactive-images' ), $wpdb->last_error ); print '<br />'; }

			if( $error ) {
				print '<div id="message" class="error"><p>' . __( '<strong>Error:</strong>', 'interactive-images' ) . $error . '</p></div>';
			} else {
				print '<div id="message" class="updated"><p>' . __( '<strong>Interactive Image saved.</strong>', 'interactive-images' ) . '</p></div>';
			}
		}
	}

	if( $icid && $error_up !== false ) {
		// load values from database
		global $wpdb;
		$image = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->interactive_images} WHERE image_id = %d", $icid ), OBJECT );
		$captions = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->interactive_captions} WHERE caption_parent = %d ORDER BY caption_id ASC", $icid ), ARRAY_A );
	}

	if( strlen( $image->image_file ) > 0 ) {
		/* There is an image */
		print '<h2>' . __( 'Edit Interactive Image', 'interactive-images' ) . '</h2>';

		print '<div id="poststuff" class="metabox-holder has-right-sidebar">';
		print '<form action="admin.php?page=interactive_form&mode=edit&icid=' . $icid . '" method="post">';
		print '<input type="hidden" name="icid" value="' . $icid . '" />';

		print '<div id="side-info-column" class="inner-sidebar">';
			print '<div class="postbox">';
				print '<div class="handlediv"><br /></div><h3 class="hndle"><span>' . __( 'Publish', 'interactive-images' ) . '</span></h3>';
				print '<div class="inside">';
					print '<p><input type="submit" name="iimage_save" value="' . __( 'Save and publish', 'interactive-images' ) . '" class="button"></p>';
				print '</div>';
			print '</div>';
			/* Image Options */
			print '<div class="postbox">';
				print '<div class="handlediv"><br /></div><h3 class="hndle"><span>' . __( 'Image Options', 'interactive-images' ) . '</span></h3>';
				print '<div class="inside">';
					print '<p><strong>' . __( 'Box width', 'interactive-images' ) . '</strong><br />' . __( 'Box width for the notes in the image. Insert in pixels.', 'interactive-images' ) . '</p>';
					if( $image->image_box_width < 1 ) { $image->image_box_width = get_option( 'interactive_images_default_box_width' ); }
					print '<p><input type="text" id="image_box_width" name="opts[image_box_width]" size="28" value="' . $image->image_box_width . '" /></p>';
					print '</select></p>';
#					print '<p><strong>' . __( 'Print captions', 'interactive-images' ) . '</strong> (not yet implemented)</p>';
#					print '<p><strong>' . __( 'Upload New Image', 'interactive-images' ) . '</strong> (not yet implemented)</p>';
				print '</div>';
			print '</div>';
		print '</div>';

		print '<div id="post-body-content">';
			if( strlen( $image->image_title ) < 1 ) { $image->image_title = __( "Interactive Image", 'interactive-images' ) . " #" . $icid; }
			print '<div id="titlediv"><input type="text" name="opts[image_title]" size="30" tabindex="1" value="' . $image->image_title . '" id="title" autocomplete="off" placeholder="' . __( 'Enter title here', 'interactive-images' ) . '" /></div>';

			print '<div class="group">';
			print '<p style="text-align: right; float: right; margin-top: 5px;"><a href="#" class="button" id="iimage_new_caption">' . __( 'Add New Caption', 'interactive-images' ) . '</a></p>';
			print '</div>';

			print '<table class="widefat" id="iimage_captions_table" style="clear: none;">';
				print '<thead><tr>';
					print '<th style="width: 15px; text-align: center;"><img src="' . plugins_url( 'interactive-images' ) . '/images/icon_reposition.png" title="' . __( 'Reposition caption', 'interactive-images' ) . '" /></th>';
					print '<th style="width: 70px;">' . __( 'Position X', 'interactive-images' ) . '</th>';
					print '<th style="width: 70px;">' . __( 'Position Y', 'interactive-images' ) . '</th>';
					print '<th>' . __( 'Description', 'interactive-images' ) . '</th>';
					print '<th style="width: 50px;">' . __( 'Actions', 'interactive-images' ) . '</th>';
				print '</tr></thead>';
				print '<tbody>';
				foreach( $captions as $caption ) {
					print '<tr>';
					print '<td class="iimage_id" style="vertical-align: middle; text-align: center;"><input type="radio" class="captions_radio" name="captions" value="' . $caption['caption_id'] . '" /></td>';
					print '<td class="iimage_x"><input type="text" id="capt_' . $caption['caption_id'] . '_x" name="caption[' . $caption['caption_id'] . '][caption_x]" value="' . $caption['caption_x'] . '" style="width: 70px;" /></td>';
					print '<td class="iimage_y"><input type="text" id="capt_' . $caption['caption_id'] . '_y" name="caption[' . $caption['caption_id'] . '][caption_y]" value="' . $caption['caption_y'] . '" style="width: 70px;" /></td>';
					print '<td class="iimage_text"><input type="text" id="capt_' . $caption['caption_id'] . '_text" name="caption[' . $caption['caption_id'] . '][caption_text]" value="' . $caption['caption_text'] . '" style="width: 100%;" /></td>';
					print '<td><span class="delete delete-iimage"><a href="#">' . __( 'Delete', 'interactive-images' ) . '</a></span></td>';
					print '</tr>';
				}
				print '</tbody>';
			print '</table>';

			print '<h2>' . __( 'Preview', 'interactive-images' ) . '</h2>';

			print InteractiveImagesFooter( $image );
			print '<div id="iimage_preview">';
			print '<div id="iimage_' . $image->image_id . '" width="' . $image->image_width . '" height="' . $image->image_height . '">';
			$uppath = wp_upload_dir( );
			print '<img src="' . $uppath['baseurl'] . '/' . get_option( 'interactive_images_upload_dir' ) . '/' . $image->image_file . '" alt="" />';
			print '</div>';
			print '</div>';
		print '</div>';

		print '</form>';
		print '</div>';
	} else {
		/* There is no image */
		print '<h2>' . __( 'New Interactive Image', 'interactive-images' ) . '</h2>';

		$uppath = wp_upload_dir( );
		if( is_writeable( $uppath['basedir'] ) ) {
			print __( "<p>Great to see you here! Let's start with uploading an image.</p>", "interactive-images" );
			print '<form method="post" action="admin.php?page=interactive_form&mode=new" enctype="multipart/form-data">';
			print '<p><input type="file" name="iimage_upload" id="iimage_upload" /></p>';
			print '<p><input type="submit" value="' . __( 'Upload image', 'interactive-images' ) . '" class="button" /></p>';
			print '</form>';
		} else {
			print sprintf( __( '<p><strong>Error:</strong> %s is not writeable</p>', 'interactive-images' ), $uppath['basedir'] );
		}
	}

	print '</div>'; // </.wrap>
}

/*  Add some help for the user
 *
 */

function InteractiveImagesHelp( ) {
	$help_shortcode = __( "<h3>Using the Interactive Images shortcode</h3><p>To use the Interactive Images shortcode as is in your content, just type <em>[iimage id=image_id]</em>. You can overwrite the image options with shortcode attributes: <em>[iimage id=image_id box=120]</em> will print 120 pixel wide boxes.", "interactive-images" );
	$help_captions = __( "<h3>(Re)positioning captions</h3><p>After adding caption(s), select a radiobutton and point-and-click the preview image to update the position fields.<p>", 'interactive-images' );

	$iimage_captions = array(
		'title' => _x( 'Captions', 'help topic', 'interactive-images' ),
		'id' => 'iimage_help_captions',
		'content' => $help_captions
	);

	$iimage_shortcode = array(
		'title' => _x( 'Shortcode', 'help topic', 'interactive-images' ),
		'id' => 'iimage_help_shortcode',
		'content' => $help_shortcode
	);

	if( get_current_screen( )->id == "interactive-images_page_interactive_form" ) { get_current_screen( )->add_help_tab( $iimage_captions ); }
	get_current_screen( )->add_help_tab( $iimage_shortcode );
}

?>
