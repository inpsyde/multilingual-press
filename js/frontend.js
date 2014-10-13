( function( $ ) {
	var mlp_quicklink = {
		init : function () {
			$( '#mlp_quicklink_container').submit( function() {
				$(this).attr( 'method', 'get' );
				document.location.href = $(this).find( 'option:selected' ).val();
				return false;
			});
		}
	};
	$( document ).ready( function( $ ) { mlp_quicklink.init(); } );
} )( jQuery );