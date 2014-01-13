var photoslider_timers = [ ];
var photoslider_timeouts = [ ];
var photoslider_transitions = [ ];

function runPhotoslider( opt ) {
	var i_id = '#' + opt.id;

	/* add timer for the slider if there is more than one photo and if we want to advance automatically */
	if( jQuery( i_id + ' ul' ).children( ).length > 1 && opt.timeout > 0 ) {
		photoslider_timers[opt.id] = setTimeout( "changeImage( 'next', '" + opt.id + "', '" + opt.timeout + "' )", opt.timeout );
		photoslider_timeouts[opt.id] = opt.timeout;
	}

	/* if the user wants to force max image size, make sure all the images match that size */
	if( opt.forcemaxsize == "true" ) {
		forceImageDimensions( i_id, opt.size );
	}

	photoslider_transitions[opt.id] = opt.transition;
}

function changeImage( direction, instance_id, timeout ) {
	if( this.isActive == true ) {
		var i_id = '#' + instance_id;
		var transition = photoslider_transitions[instance_id];

		if( direction == 'prev' ) {
			/* previous */
			hasPrevChildren = jQuery( i_id + ' .active' ).prev( 'li' ).length;
			if( hasPrevChildren == 0 ) {
				nextItem = jQuery( i_id + ' .last' );
			} else {
				nextItem = jQuery( i_id + ' .active' ).prev( 'li' );
			}
		} else {
			/* next */
			hasNextChildren = jQuery( i_id + ' .active' ).next( 'li' ).length;
			if( hasNextChildren == 0 ) {
				nextItem = jQuery( i_id + ' .first' );
			} else {
				nextItem = jQuery( i_id + ' .active + li' );
			}
		}

		nextImg = nextItem.children( '.image img' );

		/* position the controls */
		jQuery( i_id + ' .c-next' ).css( 'left', nextImg.attr( 'width' ) - jQuery( i_id + ' .c-next' ).outerWidth( ) );
		jQuery( i_id + '.ctrl-ontop .c-next' ).css( 'left', nextImg.attr( 'width' ) - jQuery( i_id + ' .c-next' ).outerWidth( ) - 10 );

		/* hide the old element */
		jQuery( i_id + ' .active p' ).fadeOut( 'slow' );

		if ( transition == 'slideleft' ) {
			jQuery( i_id + ' .active' ).effect( 'slide', { mode: 'hide' }, 2000 );
		} else if ( transition == 'slideright' ) {
			jQuery( i_id + ' .active' ).effect( 'slide', { mode: 'hide', direction: 'right' }, 2000 );
		} else if ( transition == 'fadefast' ) {
			jQuery( i_id + ' .active' ).fadeOut( 800 );
		} else {
			jQuery( i_id + ' .active' ).fadeOut( 2000 );
		}

		jQuery( i_id + ' .active' ).removeClass( 'active' );
	
		/* show next element */
		nextItem.addClass( 'active' ); 
		jQuery( i_id + ' .active p' ).fadeIn( 'slow' );

		if ( transition == 'slideleft' ) {
			jQuery( i_id + ' .active' ).effect( 'slide', { mode: 'show', direction: 'right' }, 2000 );
		} else if ( transition == 'slideright' ) {
			jQuery( i_id + ' .active' ).effect( 'slide', { mode: 'show' }, 2000 );
		} else if ( transition == 'fadefast' ) {
			jQuery( i_id + ' .active' ).fadeIn( 800 );
		} else {
			jQuery( i_id + ' .active' ).fadeIn( 2000 );
		}
	}

	/* set the timeout for next transition */
	if( timeout > 0 ) {
		photoslider_timers[instance_id] = setTimeout( "changeImage( 'next', '" + instance_id + "', '" + timeout + "' )", timeout );
	}
}

function getDimensions( id ) {
	var max_width = 0, max_height = 0, title_height = 0, cur_width, cur_height;

	/* cycle through all the images to fetch the maximum dimensions needed */
	jQuery( id + ' li' ).each( function( i, v ) {
		cur_width = jQuery( id + ' li img' ).attr( 'width' );
		cur_height = parseInt( jQuery( id + ' li img' ).attr( 'height' ) ) + parseInt( jQuery( id + ' li .captions' ).height( ) );

		if( cur_width > max_width ) {
			max_width = cur_width;
		}
		if( cur_height > max_height ) {
			max_height = cur_height;
		}
	} );

	/* get the title height and add it to the max height value */
	title_height = jQuery( id + ' .title' ).height( );
	max_height = max_height + parseInt( title_height );

	/* get height for controls if set to "above" */
	if( jQuery( id + ' .controls.above a' ).outerHeight( ) > 0 ) {
		max_height = max_height + parseInt( jQuery( id + ' .controls.above a' ).outerHeight( ) );
	}

	/* set the dimensions for the wrapper */
	jQuery( id ).parent( '.ps_wrap' ).css( 'width', max_width );
	jQuery( id ).parent( '.ps_wrap' ).css( 'height', max_height );
}

function forceImageDimensions( id, size ) {
	var dimensions = size.split( 'x' );

	jQuery( id + ' li img' ).each( function( i, v ) {
		if( jQuery( this ).width( ) > dimensions[0] ) {
			/* too wide */
			var new_height = jQuery( this ).height( ) * dimensions[0] / jQuery( this ).width( );

			jQuery( this ).attr( 'width', dimensions[0] );
			jQuery( this ).attr( 'height', new_height );

			jQuery( this ).closest( 'li' ).attr( 'width', dimensions[0] );
			jQuery( this ).closest( 'li' ).attr( 'height', new_height );
		}
	} );
}

jQuery( function( ) {
	/* show captions and controls */
	jQuery( ".photoslider .captions" ).show( );
	jQuery( ".photoslider .controls" ).show( );

	/* set the window activity state; if not active, don't progress */
	window.isActive = true;
	jQuery( window ).focus( function ( ) { this.isActive = true; } );
	jQuery( window ).blur( function ( ) { this.isActive = false; } );

	/* initialize all sliders on the page */
	var sliders = jQuery( '.photoslider' );
	jQuery.each( sliders, function( i ) {
		var cur_slider = '#' + jQuery( this ).attr( 'id' );

		/* add class for the last li */
		jQuery( cur_slider + ' li' ).last( ).addClass( 'last' );

		/* get the max dimensions needed for the wrapper */
 		getDimensions( cur_slider );

		/* position controls */
		jQuery( cur_slider + '.ctrl-above .c-next' ).css( 'left', jQuery( cur_slider + ' .active img' ).attr( 'width' ) - jQuery( cur_slider + ' .c-next' ).outerWidth( ) );
		jQuery( cur_slider + '.ctrl-ontop .c-next' ).css( 'left', jQuery( cur_slider + ' .active img' ).attr( 'width' ) - jQuery( cur_slider + ' .c-next' ).outerWidth( ) );
	} );

	/* bind next+prev buttons to click events */
	jQuery( '.photoslider .c-prev' ).click( function( e ) {
		var slider = jQuery( this ).parent( 'div' ).parent( '.photoslider' ).attr( 'id' );
		clearTimeout( photoslider_timers[slider] );
		changeImage( 'prev', slider, photoslider_timeouts[slider] );
		e.preventDefault( );
	} );
	jQuery( '.photoslider .c-next' ).click( function( e ) {
		var slider = jQuery( this ).parent( 'div' ).parent( '.photoslider' ).attr( 'id' );
		clearTimeout( photoslider_timers[slider] );
		changeImage( 'next', slider, photoslider_timeouts[slider] );
		e.preventDefault( );
	} );
} );
