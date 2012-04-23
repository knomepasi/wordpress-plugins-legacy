(function ($) {
	$( window ).load( function( ) {
		/* User selects radio button */
		$( ".captions_radio" ).click( function( e ) {
			$( ".iimage_caption" ).removeClass( "iimage_reposition" );
			// change the selected id button to red here...
			var capt_id = $( ".captions_radio:checked" ).val( );
			$( "#iimage_caption_" + capt_id ).addClass( "iimage_reposition" );
		} );

		/* Set coordinations */
		$( "#iimage_preview" ).mousedown( function( e ) {
			var capt_id = $( ".captions_radio:checked" ).val( );

			if( capt_id > 0 ) {
				var pos = $( "#iimage_preview" ).children( "div" ).offset( ),
				    new_top = e.pageY - pos.top,
				    new_left = e.pageX - pos.left;

				$( "#capt_" + capt_id + "_y" ).val( function( index, value ) { return new_top; } );
				$( "#capt_" + capt_id + "_x" ).val( function( index, value ) { return new_left; } );

				$( ".captions_radio" ).removeAttr( "checked" );

				refreshImgCaptPreview( );

				e.preventDefault( );
			}
		} );

		/* Add new caption row */
		$( "#iimage_new_caption" ).click( function( e ) {
			var table = $( "#iimage_captions_table" );

			// a big enough and unique id
			// note that this id should not be saved to the database, as it might not be tablewise unique
			var biggest = parseInt( $( "#iimage_captions_table tbody > tr:last > td:first > input:first" ).val( ) );
			if( biggest > 0 ) {
				var new_id = biggest + 1;
			} else {
				var new_id = 1;
			}

			// desired content
			var row = [
				'<td class="iimage_id" style="vertical-align: middle; text-align: center;"><input type="radio" class="captions_radio" name="captions" value="' , new_id , '" checked="checked" /></td>' ,
				'<td class="iimage_y"><input type="text" id="capt_' , new_id , '_y" name="caption_new[' , new_id , '][caption_y]" value="10" style="width: 70px;" /></td>' ,
				'<td class="iimage_x"><input type="text" id="capt_' , new_id , '_x" name="caption_new[' , new_id , '][caption_x]" value="10" style="width: 70px;" /></td>' ,
				'<td class="iimage_text"><input type="text" id="capt_' , new_id , '_text" name="caption_new[' , new_id , '][caption_text]" value="" placeholder="Enter description here" style="width: 100%;" /></td>' ,
				'<td><span class="delete delete-imgcapt"><a href="#">Delete</a></span></td>'
			].join("");

			var tr = $( "<tr />", {
				'html': row
			} ).appendTo( table );

			tr.find( ".iimage_text input" ).focus( );

			refreshImgCaptPreview( );
			e.preventDefault( );
		} );

		/* Delete caption row */
		$( "#iimage_captions_table .delete-iimage a" ).live( 'click', ( function( e ) {
			$( this ).parent( "span" ).parent( "td" ).parent( "tr" ).remove( );

			refreshImgCaptPreview( );
			e.preventDefault( );
		} ) );

		/* User edits captions */
		$( "#iimage_captions_table input[type='text']" ).live( 'change', function( ) {
			refreshImgCaptPreview( );
		} );
		/* User edits box width */
		$( "#image_box_width" ).live( 'change', function( ) {
			refreshImgCaptPreview( );
		} );
	} );

	window.refreshImgCaptPreview = function ( ) {
		// remove all div's inside the main div
		var image = $( "#iimage_preview > div" );
		image.children( "div" ).remove( );
		var image_id = image.attr( "id" );

		// read the table and create a new array and call printing function with it again
		var new_captions = [{}];
		var i = 0;
		$( "#iimage_captions_table > tbody > tr" ).each( function( index ) {
			var $this = $(this), // Cache $(this)
			    capt_id = parseInt( $this.find( ".iimage_id input" ).val( ) ),
			    pos_y = parseInt( $this.find( ".iimage_y input" ).val( ) ),
			    pos_x = parseInt( $this.find( ".iimage_x input" ).val( ) ),
			    text = $this.find( ".iimage_text input" ).val( );

			new_captions[i] = { "parent": image_id, "pos_x": pos_x, "pos_y": pos_y, "text": text, "id": capt_id };

			i = i + 1;
		} );

		// get options here
		var box_width = $( "#image_box_width" ).val( );
		new_options = { "box_width": box_width };

		findCaptions( new_captions, new_options );
	}

	window.delInteractiveImage = function ( title ) {
		return confirm( 'Are you sure you want to delete "' + title + '" permanently?' );
	}
}(jQuery));