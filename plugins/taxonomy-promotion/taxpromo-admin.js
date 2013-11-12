jQuery( window ).load( function( ) {
	TaxonomyPromotion( );
} );

jQuery( document ).ajaxSuccess( function( ) {
	TaxonomyPromotion( );
} );

function TaxonomyPromotion( ) {
	var ourTax;

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
				alert( 'Error, see console' );
				console.log( error );
			}
		} );
	} );
}

function PopulateTermDropdown( ID, value, data ) {
	jQuery( ID ).empty( );

	if( value != 0 ) {
		jQuery( ID ).prop( 'disabled', false );

		for( var i = 0, len = data.length; i < len; i++ ) {
			var term = data[i];
			jQuery( ID ).append( '<option value="' + term.term_id + '">' + term.name + '</option>' );
		}
	} else {
		jQuery( ID ).append( '<option value="+">' + l10n.selecttax + '</option>' );
		jQuery( ID ).prop( 'disabled', true );
	}
}