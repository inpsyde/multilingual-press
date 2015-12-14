;( function( $ ) {
	'use strict';

	var pluginActivator = {

		initialize: function() {
			var $select = $( '#inpsyde_multilingual_based' ),
				$row = $( '#blog_activate_plugins' ).closest( 'tr' );

			if ( ! $select.length ) {
				return;
			}

			if ( ! $row.length ) {
				return;
			}

			pluginActivator.$row = $row;

			$select.on( 'change', pluginActivator.toggleRow );
		},

		toggleRow: function() {
			pluginActivator.$row.toggle( $( this ).val() > '0' );
		}
	};

	$( setTimeout( pluginActivator.initialize, 300 ) );

} )( jQuery );
