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

		// Toggle handler
		setToggle: function() {
			$( document ).on( 'click', '[data-toggle_selector]', function( event ) {
				var $this = $( this ),
					$toggle_container = $( $this.data( 'toggle_selector' ) );

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

;( function( $ ) {
	"use strict";

	$.fn.mlp_search = function( options ) {

		var settings = $.extend( {
				remote_blog_id  : this.data( 'remote_blog_id' ),
				remote_post_id  : this.data( 'remote_post_id' ),
				source_blog_id  : this.data( 'source_blog_id' ),
				source_post_id  : this.data( 'source_post_id' ),
				search_field    : 'input.mlp_search_field',
				result_container: 'ul.mlp_search_results',
				action          : 'mlp_search',
				nonce           : '',
				spinner         : '<span class="spinner no-float" style="display:block"></span>'
			}, options ),

			original_content = $( settings.result_container ).html(),
			$search_field = $( settings.search_field ),
			stored = [],

			insert = function( content ) {
				$( settings.result_container ).html( content );
			},

			fetch = function( keywords ) {
				if ( stored[ keywords ] ) {
					insert( stored[ keywords ] );

					return;
				}

				insert( settings.spinner );

				var ajax = $.post(
					ajaxurl,
					{
						action        : settings.action,
						source_post_id: settings.source_post_id,
						source_blog_id: settings.source_blog_id,
						remote_post_id: settings.remote_post_id,
						remote_blog_id: settings.remote_blog_id,
						s             : keywords
					}
				);

				ajax.done( function( data ) {
					stored[ keywords ] = data;
					insert( data );
				} );
			};

		// Prevent submission via Enter key
		$search_field.on( 'keypress', function( event ) {
			if ( 13 == event.which ) {
				return false;
			}
		} ).on( 'keyup', function( event ) {
			event.preventDefault();
			event.stopPropagation();

			var str = $.trim( $( this ).val() );

			if ( !str || 0 === str.length ) {
				insert( original_content );
			} else if ( 2 < str.length ) {
				fetch( str );
			}
		} );
	};

	$( '.mlp_rsc_save_reload' ).on( 'click.mlp', function( event ) {
		event.preventDefault();
		event.stopPropagation();

		var $this = $( this ),
			source_post_id = $this.data( 'source_post_id' ),
			source_blog_id = $this.data( 'source_blog_id' ),
			remote_post_id = $this.data( 'remote_post_id' ),
			remote_blog_id = $this.data( 'remote_blog_id' ),
			current_value = $( 'input[name="mlp_rsc_action[' + remote_blog_id + ']"]:checked' ).val(),
			new_post_id = 0,
			new_post_title = '',

			disconnect = function() {
				changeRelationship( 'disconnect' );
			},

			newRelation = function() {
				new_post_title = $( 'input[name="post_title"]' ).val();
				changeRelationship( 'new_relation' );
			},

			connectExisting = function() {
				new_post_id = $( 'input[name="mlp_add_post[' + remote_blog_id + ']"]:checked' ).val();

				if ( !new_post_id || '0' === new_post_id ) {
					alert( 'Please select a post.' );
				} else {
					changeRelationship( 'connect_existing' );
				}
			},

			changeRelationship = function( action ) {
				// We use jQuery's ajax function (and not $.post) due to synchrony
				$.ajax( {
					type   : 'POST',
					url    : ajaxurl,
					data   : {
						action        : 'mlp_rsc_' + action,
						source_post_id: source_post_id,
						source_blog_id: source_blog_id,
						remote_post_id: remote_post_id,
						remote_blog_id: remote_blog_id,
						new_post_id   : new_post_id,
						new_post_title: new_post_title
					},
					success: function() {
						window.location.reload( true );
					},
					async  : false
				} );
			};

		if ( !current_value || 'stay' == current_value ) {
			return;
		}

		switch ( current_value ) {
			case 'disconnect':
				disconnect();
				break;

			case 'new':
				newRelation();
				break;

			case 'search':
				connectExisting();
				break;
		}
	} );

} )( jQuery );
