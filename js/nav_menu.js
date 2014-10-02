/**
 * Handle the custom post type nav menu meta box
 */
jQuery( document ).ready( function($) {
	$( '#submit-mlp_language' ).click( function( event ) {
		event.preventDefault();

		var items      = $( '#' + mlp_nav_menu.metabox_list_id + ' li :checked' ),
			submit     = $( '#submit-mlp_language' ),
			languages  = [],
			post_data  = { action: mlp_nav_menu.action },
			menu_id    = $( '#menu' ).val();

		items.each( function() {
			languages.push( $( this ).val() );
		} );

		// Show spinner
		$( '#' + mlp_nav_menu.metabox_id ).find('.spinner').show();

		// Disable button
		submit.prop( 'disabled', true );

		post_data[ "mlp_sites" ]             = languages;
		post_data[ mlp_nav_menu.nonce_name ] = mlp_nav_menu.nonce;
		post_data[ "menu" ] = menu_id;

		// Send checked post types with our action, and nonce
		$.post( mlp_nav_menu.ajaxurl, post_data,

			// AJAX returns html to add to the menu, hide spinner, remove checks
			function( response ) {
				$( '#menu-to-edit' ).append( response );
				$( '#' + mlp_nav_menu.metabox_id ).find('.spinner').hide();
				items.prop( "checked", false);
				submit.prop( "disabled", false );
			}
		);
	});
});
