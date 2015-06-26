;( function( $ ) {
	"use strict";

	$( '#submit-mlp_language' ).on( 'click', function( event ) {
		event.preventDefault();

		var languages = [],
			$items = $( '#' + mlp_nav_menu.metabox_list_id + ' li :checked' ),
			$spinner = $( '#' + mlp_nav_menu.metabox_id ).find( '.spinner' ),
			$submit = $( '#submit-mlp_language' );

		$items.each( function() {
			languages.push( $( this ).val() );
		} );

		$submit.prop( 'disabled', true );
		$spinner.show();

		var data = {
			action   : mlp_nav_menu.action,
			mlp_sites: languages,
			menu     : $( '#menu' ).val()
		};
		data[ mlp_nav_menu.nonce_name ] = mlp_nav_menu.nonce;

		$.post( mlp_nav_menu.ajaxurl, data, function( response ) {
			$( '#menu-to-edit' ).append( response );
			$spinner.hide();
			$items.prop( 'checked', false );
			$submit.prop( 'disabled', false );
		} );
	} );

} )( jQuery );
