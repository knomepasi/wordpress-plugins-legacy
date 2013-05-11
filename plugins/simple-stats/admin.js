jQuery( function( ) {
	jQuery( '.show-more a' ).click( function( e ) {

		jQuery( '.more' ).show( 'blind' );
		jQuery( '.show-more' ).hide( );
		e.preventDefault( );
	} );
} );
