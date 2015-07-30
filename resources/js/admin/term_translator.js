;( function( $ ) {
	"use strict";

	var term_translator = {

		init: function() {
			this.isPropagating = false;

			this.propagate_term_selection();
		},

		propagate_term_selection: function() {
			var $table = $( '.mlp_term_selections' );

			if ( $table.length ) {
				var $selects = $table.find( 'select' );

				$table.on( 'change', 'select', function() {
					if ( term_translator.isPropagating ) {
						return;
					}

					term_translator.isPropagating = true;

					var $this = $( this ),
						relation = $this.find( '[value="' + $this.val() + '"]' ).data( 'relation' );

					$selects.not( $this ).each( function() {
						var $this = $( this ),
							$option = $this.find( 'option[data-relation="' + relation + '"]' );

						if ( $option.length ) {
							$this.val( $option.val() );
						} else {
							$this.val( $this.find( 'option' ).first().val() );
						}
					} );

					term_translator.isPropagating = false;
				} );
			}
		}
	};

	$( function() {
		term_translator.init();
	} );

} )( jQuery );
