;( function( $ ) {
	"use strict";

	var relChanged = [];
	var $body = $( 'body' );

	$( '.mlp_rsc_action_list input' ).on( 'change', function( event ) {
		var $this = $( this ),
			metabox = $( this ).parent( '.mlp_advanced_translator_metabox' ),
			stay = $this.val() === 'stay',
			elIndex = containsElement( relChanged, metabox );

		if ( elIndex === -1 ) {
			if ( !stay ) {
				relChanged.push( metabox );
			}

		} else {
			if ( stay ) {
				relChanged.splice( elIndex, 1 );
			}
		}
	} );

	if ( $body.hasClass( 'post-php' ) ) {
		$( '#publish' ).on( 'click', function( e ) {
			if ( relChanged.length ) {
				if ( !confirm( 'You have unsaved changes in your Post Relationships. These will be discarded if you continue' ) ) {
					e.preventDefault();
					e.stopPropagation();
				} else {
					//TODO: We may want to fire an event or call an existing function here
				}

			}
		} );
	}

	/**
	 * Checks if a jQuery object is already in an array
	 * @param array
	 * @param element
	 * @returns {number}
	 */
	function containsElement( array, element ) {
		for ( var i = 0; i < array.length; i++ ) {
			if ( array[ i ][ 0 ] === element[ 0 ] ) {
				return i;
			}
		}

		return -1;
	}

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
