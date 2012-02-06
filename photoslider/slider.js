var photoslider_timers = [ ];
var photoslider_timeouts = [ ];

function runPhotoslider( opt ) {
	var i_id = '#' + opt.id;

	/* add timer for the slider if there is more than one photo and if we want to advance automatically */
	if( jQuery( i_id + ' ul' ).children( ).length > 1 && opt.timeout > 500 ) {
		photoslider_timers[opt.id] = setTimeout( "changeImage( 'next', '" + opt.id + "', '" + opt.timeout + "' )", opt.timeout );
		photoslider_timeouts[opt.id] = opt.timeout;
	}
}

function changeImage( direction, instance_id, timeout ) {
	if( this.isActive == true ) {
		var i_id = '#' + instance_id;

		if( direction == 'prev' ) {
			/* prev */
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

		nextImg = nextItem.children( '.image' ).children( 'img' );

		/* hide the old element */
		jQuery( i_id + ' .active p' ).fadeOut( 'slow' );
		jQuery( i_id + ' .active' ).fadeOut( 2000 );
		jQuery( i_id + ' .active' ).removeClass( 'active' );

		/* see if we need to tweak the height or width */
		new_height =
			parseInt( jQuery( i_id + ' .controls a' ).outerHeight( ) ) +
			parseInt( jQuery( i_id + " .title" ).height( ) ) +
			parseInt( nextImg.attr( 'height' ) ) +
			parseInt( jQuery( i_id + ' .captions' ).height( ) );
		new_width = parseInt( nextImg.attr( 'width' ) ) + parseInt( jQuery( i_id + ' .captions' ).width( ) );

		if( new_height > jQuery( i_id ).height( ) ) {
			jQuery( i_id ).css( 'height', new_height );
			jQuery( i_id ).parent( '.ps_wrap' ).css( 'height', new_height );
		}
		if( new_width > jQuery( i_id ).width( ) ) {
			jQuery( i_id ).css( 'width', new_width );
		}

		jQuery( i_id + ' .image' ).css( 'height', nextImg.attr( 'height' ) );
		jQuery( i_id + ' .image' ).css( 'width', parseInt( nextImg.attr( 'width' ) ) + 10 );
		jQuery( i_id ).css( 'width', parseInt( nextImg.attr( 'width' ) ) + 10 );

		jQuery( i_id + ' .c-next' ).css( 'left', nextImg.attr( 'width' ) - jQuery( i_id + ' .c-next' ).outerWidth( ) );
		jQuery( i_id + '.ctrl-ontop .c-next' ).css( 'left', nextImg.attr( 'width' ) - jQuery( i_id + ' .c-next' ).outerWidth( ) - 10 );
	
		/* show next picture */
		nextItem.addClass( 'active' ); 
		jQuery( i_id + ' .active p' ).fadeIn( 'slow' );
		jQuery( i_id + ' .active' ).fadeIn( 2000 );
	}

	/* set the timeout for next transition */
	photoslider_timers[instance_id] = setTimeout( "changeImage( 'next', '" + instance_id + "', '" + timeout + "' )", timeout );
}

jQuery( function( ) {
	window.isActive = true;
	$( window ).focus( function ( ) { this.isActive = true; } );
	$( window ).blur( function ( ) { this.isActive = false; } );

	var sliders = jQuery( '.photoslider' );
	jQuery.each( sliders, function( i ) {
		var cur_slider = '#' + jQuery( this ).attr( 'id' );

		new_height = 
			parseInt( jQuery( cur_slider + ' .controls a' ).outerHeight( ) ) +
			parseInt( jQuery( cur_slider + ' .title' ).height( ) ) +
			parseInt( jQuery( cur_slider + ' li img' ).first( ).attr( 'height' ) ) +
			parseInt( jQuery( cur_slider + ' .captions' ).height( ) );
		new_width = jQuery( cur_slider + ' li img' ).first( ).attr( 'width' );

		jQuery( cur_slider ).css( 'height', new_height );
		jQuery( cur_slider ).css( 'width', new_width );

		jQuery( cur_slider ).parent( '.ps_wrap' ).css( 'height', new_height );

		jQuery( cur_slider + ' .image' ).css( 'height', jQuery( cur_slider + ' li img' ).first( ).attr( 'height' ) );
		jQuery( cur_slider + ' .image' ).css( 'width', parseInt( jQuery( cur_slider + ' li img' ).first( ).attr( 'width' ) ) + 10 );

		jQuery( cur_slider + '.ctrl-above .c-next' ).css( 'left', jQuery( cur_slider + ' .first img' ).attr( 'width' ) - jQuery( cur_slider + ' .c-next' ).outerWidth( ) );
		jQuery( cur_slider + '.ctrl-ontop .c-next' ).css( 'left', jQuery( cur_slider + ' .first img' ).attr( 'width' ) - jQuery( cur_slider + ' .c-next' ).outerWidth( ) - 10 );

		jQuery( cur_slider + ' li' ).last( ).addClass( 'last' );
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

