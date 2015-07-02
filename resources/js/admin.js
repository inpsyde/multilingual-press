;( function( $ ) {
	"use strict";

	var multilingualPress = {

		init     : function() {
			var self = this;
			self.setToggle();
			/**
			 * Add event handler for copy post buttons
			 */
			$( document ).on( 'click', '.mlp_copy_button', function( event ) {
				event.preventDefault();
				var blogId = $( event.target ).data( 'blog_id' );
				self.copyPost( blogId );

			} );

		},

		// Toggle handler, show/hide elements with class 'mlp_toggler'
		setToggle: function() {
			$( document ).on( 'click', '.mlp_toggler', function( event ) {
				var $this = $( this ),
					$toggle_container = $( $this.data( 'toggle_selector' ) );

				if ( 'submit' === this.type || 'A' === this.tagName ) {
					event.preventDefault();
					event.stopPropagation();

					$toggle_container.toggle();

					return false;
				}

				if ( 'LABEL' === this.tagName ) {
					var $target = $( '#' + $this.attr( 'for' ) );

					event.stopPropagation();

					// TODO: Get rid of this nested event handler binding
					$( 'input[name="' + $target.attr( 'name' ) + '"]' ).on( 'change', function() {
						$toggle_container.toggle( $target.val() === $( this ).val() );

						return true;
					} );

					return true;
				}

				$toggle_container.toggle();

				return false;
			} );
		},

		// Copy post buttons next to media buttons
		copyPost : function( blogId ) {

			// @formatter:off

			var prefix = 'mlp_translation_data_' + blogId,
				translationContent = tinyMCE.get( prefix + '_content' ),
				content = $( '#content' ).val(), // plain content for "text"-view,
				excerpt = $( '#excerpt' ).val(), // plain content for "text"-view,
				tinyMCEContent = tinyMCE.get( 'content' ),
				title = $( '#title' ).val(),
				postSlug = $( '#editable-post-name' ).html();

			if ( title ) {
				$( '#' + prefix + '_title' ).val( title );
			}

			if ( content ) {
				$( '#' + prefix + '_content' ).val( content );
			}

			if ( postSlug ) {
				$( '#' + prefix + '_name' ).val( postSlug );
			}

			if ( excerpt ) {
				$( '#' + prefix + '_excerpt' ).val( excerpt );
			}

			if ( tinyMCEContent ) {
				translationContent.setContent( tinyMCEContent.getContent() );
			}
			// @formatter:on
		}
	};

	$( function() {
		multilingualPress.init();
	} );

} )( jQuery );
