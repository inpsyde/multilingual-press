;( function( $ ) {
	"use strict";

	var mlp_quicklink = {
		init: function() {
			$( '#mlp_quicklink_container' ).on( 'submit', function() {
				var $this = $( this );

				$this.attr( 'method', 'get' );
				document.location.href = $this.find( 'option:selected' ).val();

				return false;
			} );
		}
	};

	$( function() {
		mlp_quicklink.init();
	} );

} )( jQuery );
