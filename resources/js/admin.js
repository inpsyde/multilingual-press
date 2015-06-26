(
	function( $ ) {
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
			copyPost : function( blogId ) {

				// @formatter:off

				var prefix = 'mlp_translation_data_' + blogId,
					translationContent = tinyMCE.get( prefix + '_content' ),
					content = $( '#content' ).val(), // plain content for "text"-view,
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

				if ( tinyMCEContent ) {
					translationContent.setContent( tinyMCEContent.getContent() );
				}

				/**
				 * TODO: Figure out how to make this functionality more extensible and accessible
				 *
				 * The code below is a quick'n dirty draft that would provide a simple way to add
				 * custom code with an easy access to relevant data.
				 * Until there's consensus on how to extend the advanced Translator API, better keep it disabled
				 *
				 */
				//$( document ).trigger( 'mlp_copy_post', {
				//	blogId: blogId,
				//	prefix: prefix,
				//	data  : {
				//		translationContent: translationContent,
				//		content           : content,
				//		title             : title,
				//		postSlug          : postSlug
				//	}
				//} );
				// @formatter:on
			}
		};

		$( function() {
			multilingualPress.init();
		} );

	}
)( jQuery );
