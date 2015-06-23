;( function( $ ) {
	"use strict";

	var multilingualPress = {

		init     : function() {
			this.setToggle();
			this.copyPost();
		},

		// Toggle handler, show/hide elements with class 'mlp_toggler'
		setToggle: function() {
			$( document ).on( 'click', '.mlp_toggler', function( event ) {
				var $this = $( this ),
					$toggle_container = $( $this.data( 'toggle_selector' ) );

				if ( 'submit' == this.type || 'A' == this.tagName ) {
					event.preventDefault();
					event.stopPropagation();

					$toggle_container.toggle();

					return false;
				}

				if ( 'LABEL' == this.tagName ) {
					var $target = $( '#' + $this.attr( 'for' ) );

					event.stopPropagation();

					$( 'input[name="' + $target.attr( 'name' ) + '"]' ).on( 'change', function() {
						$toggle_container.toggle( $target.val() == $this.val() );

						return true;
					} );

					return true;
				}

				$toggle_container.toggle();

				return false;
			} );
		},

		// Copy post buttons next to media buttons
		copyPost : function() {
			$( document ).on( 'click', '.mlp_copy_button', function( event ) {
				event.preventDefault();

				// @formatter:off
				var blog_id = $( this ).data( 'blog_id' ),
					prefix = 'mlp_translation_data_' + blog_id,
					translationContent = tinyMCE.get( prefix + '_content' ),
					content = $( '#content' ).val(), // plain content for "text"-view,
					tinyMCEContent = tinyMCE.get( 'content' ),
					title = $( '#title' ).val();

				if ( title ) {
					$( '#' + prefix + '_title' ).val( title );
				}

				if ( content ) {
					$( '#' + prefix + '_content' ).val( content );
				}

				if ( tinyMCEContent ) {
					translationContent.setContent( tinyMCEContent.getContent() );
				}
				// @formatter:on
			} );
		}
	};

	$( function() {
		multilingualPress.init();
	} );

} )( jQuery );
