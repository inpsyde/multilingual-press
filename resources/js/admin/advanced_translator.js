;( function( $ ) {
	"use strict";

	var advanced_translator = {

		init: function() {
			this.meta_box_init();
			this.meta_box_toggle_switch();
		},

		meta_box_init: function() {
			$( '.to_translate' ).hide();

			$( 'input.do_translate[checked]' ).each( function() {
				var data = $( this ).attr( 'data' );

				$( '.translate_' + data ).toggle();
				$( '#content_' + data + '_ifr' ).height( 400 );
			} );
		},

		meta_box_toggle_switch: function() {
			$( '.do_translate' ).on( 'click', function() {
				var data = $( this ).attr( 'data' );

				$( '.translate_' + data ).toggle( 'slow' );
				$( '#content_' + data + '_ifr' ).height( 400 );
			} );
		}
	};

	$( function() {
		advanced_translator.init();
	} );

} )( jQuery );
