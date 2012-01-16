jQuery( window ).load( function( ) {
	/* User selects radio button */
	jQuery( ".captions_radio" ).click( function( e ) {
		jQuery( ".iimage_caption" ).removeClass( "iimage_reposition" );
		// change the selected id button to red here...
		var capt_id = jQuery( ".captions_radio:checked" ).val( );
		jQuery( "#iimage_caption_" + capt_id ).addClass( "iimage_reposition" );
	} );

	/* Set coordinations */
	jQuery( "#iimage_preview" ).mousedown( function( e ) {
		var capt_id = jQuery( ".captions_radio:checked" ).val( );

		if( capt_id > 0 ) {
			var img_left = parseInt( jQuery( "#iimage_preview" ).children( "div" ).children( "img" ).position( ).left );
			var img_top = parseInt( jQuery( "#iimage_preview" ).children( "div" ).children( "img" ).position( ).top );

			jQuery( "#capt_" + capt_id + "_y" ).val( function( index, value ) { return e.pageY - img_top; } );
			jQuery( "#capt_" + capt_id + "_x" ).val( function( index, value ) { return e.pageX - img_left; } );

			jQuery( ".captions_radio" ).removeAttr( "checked" );

			refreshImgCaptPreview( );

			e.preventDefault( );
		}
	} );

	/* Add new caption row */
	jQuery( "#iimage_new_caption" ).click( function( e ) {
		var table = jQuery( "#iimage_captions_table" );

		// a big enough and unique id
		// note that this id should not be saved to the database, as it might not be tablewise unique
		var biggest = parseInt( jQuery( "#iimage_captions_table tbody tr" ).last( ).children( "td" ).first( ).children( "input" ).first( ).val( ) );
		if( biggest > 0 ) {
			var new_id = biggest + 1;
		} else {
			var new_id = 1;
		}

		// desired content
		var row;
		row = '<td style="vertical-align: middle; text-align: center;"><input type="radio" name="captions" value="' + new_id + '" checked="checked" /></td>';
		row = row + '<td class="imgcapt_y"><input type="text" id="capt_' + new_id + '_y" name="caption_new[' + new_id + '][caption_y]" value="" style="width: 70px;" /></td>'; 
		row = row + '<td class="imgcapt_x"><input type="text" id="capt_' + new_id + '_x" name="caption_new[' + new_id + '][caption_x]" value="" style="width: 70px;" /></td>'; 
		row = row + '<td class="imgcapt_text"><input type="text" id="capt_' + new_id + '_text" name="caption_new[' + new_id + '][caption_text]" value="" placeholder="Enter description here" style="width: 100%;" /></td>';
		row = row + '<td><span class="delete delete-imgcapt"><a href="#">Delete</a></span></td>';

		var tr = jQuery( "<tr />", {
			'html': row
		} ).appendTo( table );

		tr.children( ".iimage_text" ).children( "input" ).focus( );

		e.preventDefault( );
	} );

	/* Delete caption row */
	jQuery( "#iimage_captions_table .delete-iimage a" ).live( 'click', ( function( e ) {
		jQuery( this ).parent( "span" ).parent( "td" ).parent( "tr" ).remove( );

		refreshImgCaptPreview( );

		e.preventDefault( );
	} ) );

	/* User edits captions */
	jQuery( "#iimage_captions_table input[type='text']" ).live( 'change', function( ) {
		refreshImgCaptPreview( );
	} );
	/* User edits box width */
	jQuery( "#image_box_width" ).live( 'change', function( ) {
		refreshImgCaptPreview( );
	} );
} );

function refreshImgCaptPreview( ) {
	// remove all div's inside the main div
	var image = jQuery( "#iimage_preview" ).children( "div" );
	image.children( "div" ).remove( );
	var image_id = image.attr( "id" );

	// read the table and create a new array and call printing function with it again
	var new_captions = [{}];
	var i = 0;
	jQuery( "#iimage_captions_table > tbody" ).children( "tr" ).each( function( index ) {
		var capt_id = parseInt( jQuery( this ).children( ".iimage_id" ).children( "input" ).val( ) );
		var pos_y = parseInt( jQuery( this ).children( ".iimage_y" ).children( "input" ).val( ) );
		var pos_x = parseInt( jQuery( this ).children( ".iimage_x" ).children( "input" ).val( ) );
		var text = jQuery( this ).children( ".iimage_text" ).children( "input" ).val( );

		new_captions[i] = { "parent": image_id, "pos_x": pos_x, "pos_y": pos_y, "text": text, "id": capt_id };

		i = i + 1;
	} );

	// get options here
	var box_width = jQuery( "#image_box_width" ).val( );
	new_options = { "box_width": box_width };

	findCaptions( new_captions, new_options );

 	jQuery( "#iimage_preview" ).forceRedraw( true );
}

function delInteractiveImage( title ) {
	var a = confirm( 'Are you sure you want to delete "' + title + '" permanently?' );
	return a;
}

