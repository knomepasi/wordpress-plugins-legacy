function findCaptions( cap, opt ) {
	jQuery.each( cap, function( cid, c ) {
		var div = jQuery( "<div/>", {
			'class': 'iimage_caption',
			'id': 'iimage_caption_' + c.id
		} ).appendTo( "#" + c.parent );
		var span = jQuery( "<span/>", {
			'class': 'c_main',
			'html': c.text
		} ).appendTo( div );

		div.css( { 'position': 'relative' } );

		/* this just the *last* fallback */
		if( opt.box_width < 1 ) { opt.box_width = 145; }

		div.css( { 'width': opt.box_width + "px" } );
		span.css( { 'width': opt.box_width + "px" } );

		var pos = div.parent( ).offset( );
		var pos_y = pos.top + c.pos_y;
		var pos_x = pos.left + c.pos_x;
		div.offset( { top: pos_y, left: pos_x } );

		// if span overlaps the bottom edge...
		var img_bottom = div.parent( ).children( "img" ).innerHeight( ) + div.parent( ).children( "img" ).position( ).top;
		var div_top = div.offset( ).top;
		var div_topmost = div.offset( ).top - div.parent( ).children( "img" ).position( ).top - div.outerHeight( ) - 15;

		if( div_top > img_bottom && div_topmost > 1 ) {
			// the div is going to be printed lower than the image, help!
			var span_height = div.height( ) + 15;
			div.children( "span.c_main" ).css( { 'margin-top': -span_height } );
		}

		// if span overlaps the right edge...
		var img_right = div.parent( ).children( "img" ).innerWidth( ) + div.parent( ).children( "img" ).position( ).left;
		var div_left = div.offset( ).left;
		var div_leftmost = div.offset( ).left - div.parent( ).children( "img" ).position( ).left - div.outerWidth( ) - 28;

		if( div_left > img_right && div_leftmost > 1 ) {
			// the div is going to be printed 'righter' than the image, help!
			var span_width = div.width( ) + 28;
			div.children( "span.c_main" ).css( { 'margin-left': -span_width } );
		}

		// check the image container
		div.parent( ).height( div.parent( ).children( "img" ).innerHeight( ) );

		//
		if( div_topmost < 0 ) {
			var bottom_point = div.offset( ).top + div.outerHeight( ) + 20 - div.parent( ).children( "img" ).position( ).top;
			if( bottom_point > div.parent( ).children( "img" ).outerHeight( ) ) {
				div.parent( ).height( bottom_point );
			}
		}
	} );

}

jQuery( window ).load( function( ) {
	// set the hover action
	// for some reason, this doesn't seem to work with wp
	jQuery( ".iimage_caption" ).hover(
		function ( ) { jQuery( this ).addClass( "hover" ) },
		function ( ) { jQuery( this ).removeClass( "hover" ) }
	);
} );
