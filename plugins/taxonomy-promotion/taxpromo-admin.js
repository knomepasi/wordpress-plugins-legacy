jQuery( window ).load( function( ) {
	jQuery( '.taxpromo-tax' ).unbind( );
	TaxonomyPromotion( );
} );

jQuery( document ).ajaxSuccess( function( ) {
	jQuery( '.taxpromo-tax' ).unbind( );
	TaxonomyPromotion( );
} );

function TaxonomyPromotion( ) {
	var ourTax;

	jQuery( '.taxpromo.hide-if-js' ).remove( );

	jQuery( '.taxpromo-tax' ).change( function( e ) {
		// a value in a taxonomy dropdown has changed!
		var ourID = "#" + jQuery( this ).attr( 'id' );
		var ourValue = jQuery( this ).attr( 'value' );
		console.log( ourValue );
		jQuery( ourID + ' :selected' ).each( function( ) {
			ourTax = jQuery( this ).attr( 'value' );
		} );

		jQuery.ajax( {
			url: WP_AJAX.url,
			data: {
				'action': 'taxpromo_ajax',
				'fn': 'get_taxonomy_terms',
				'taxonomy': ourTax
			},
			dataType: 'JSON',
			success: function( data ) {
				PopulateTermDropdown( ourID + "_term", ourValue, data );
			},
			error: function( error ) {
				console.log( error );
			}
		} );
	} );
}

function PopulateTermDropdown( ID, value, data ) {
	var selected_term = jQuery( ID + ' :selected' ).attr( 'value' );

	jQuery( ID ).empty( );

	if( value != 0 ) {
		jQuery( ID ).prop( 'disabled', false );

		for( var i = 0, len = data.length; i < len; i++ ) {
			var term = data[i];
			if( selected_term == term.term_id ) {
				jQuery( ID ).append( '<option value="' + term.term_id + '" selected="selected">' + term.name + '</option>' );
			} else {
				jQuery( ID ).append( '<option value="' + term.term_id + '">' + term.name + '</option>' );
			}
		}
	} else {
		jQuery( ID ).append( '<option value="+">' + l10n.selecttax + '</option>' );
		jQuery( ID ).prop( 'disabled', true );
	}
}