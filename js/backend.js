/** global jQuery */

/**
 * General functions
 */
( function( $ ) {

	var multilingual_press = {

		/**
		 * Initial Function
		 */
		init : function () {
			this.set_toggle();
			this.copy_post();
		},

		/**
		 * Toggle handler, show/hide elements with the class 'mlp_toggler'.
		 */
		set_toggle: function () {

			$(document).on("click", ".mlp_toggler", function (event) {

				var toggle_container = $($(this).data('toggle_selector'));

				// buttons and links
				if ('submit' == this.type || 'A' == this.tagName) {
					event.stopPropagation();
					event.preventDefault();
					toggle_container.toggle();
					return false;
				}

				// labels, needs improvements
				if ('LABEL' == this.tagName) {
					var target = $('#' + $(this).attr('for'));

					/* probably not necessary or useful
					 if ( 'radio' != target.attr( 'type' ) )
					 return true;*/

					event.stopPropagation();

					$('input[name="' + target.attr('name') + '"]').change(function () {
						toggle_container.toggle(target.val() == $(this).val());
						return true;
					});

					return true;
				}

				toggle_container.toggle();

				return false;
			});
		},

		/**
		 * Copy post buttons next to media buttons.
		 */
		copy_post: function () {

			$( document ).on( "click", ".mlp_copy_button", function ( event ) {
				event.preventDefault();

				// @formatter:off
				var blog_id		= $( this ).data( "blog_id" ),
					prefix		= "mlp_translation_data_" + blog_id,
					mce 		= tinyMCE.get( prefix + "_content" ),
					content		= $( '#content' ).val(), // plain content for "text"-view
					title		= $( "#title" ).val()
				;

				if ( title )
					$( "#" + prefix + "_title" ).val( title );

				if ( content ) {
					$( "#" + prefix + "_content" ).val( content );
					mce.setContent( content );
				}
				// @formatter:on
			});
		}
	};

	$( document ).ready( function( $ ) {
		multilingual_press.init();
	} );

} )( jQuery );

// advanced translator metaboxes
jQuery.noConflict();
( function( $ ) {
	/**
	 * Main Class for the advanced translator
	 */
	var advanced_translator = {

		/**
		 * Initialation Function
		 *
		 * @author	th
		 * @since	0.1
		 * @return	void
		 */
		init : function() {
			advanced_translator.meta_box_init();
			advanced_translator.meta_box_toggle_switch();
		},

		/**
		 * Meta Box Init function which closes all boxes
		 * and reopen the active ones
		 *
		 * @author	th
		 * @since	0.1
		 * @return	void
		 */
		meta_box_init : function() {
			// Close all
			$( '.to_translate' ).css( 'display', 'none' );

			// Get active translations
			$( 'input.do_translate[checked]' ).each( function( index, value ) {
				$( '.translate_' + $( this ).attr( 'data' ) ).toggle();
				$( '#content_' + $( this ).attr( 'data' ) + '_ifr' ).height( '400px' );
			} );
		},

		/**
		 * Meta Box Toggle Switch
		 *
		 * @author	th
		 * @since	0.1
		 * @return	void
		 */
		meta_box_toggle_switch : function() {
			$( '.do_translate' ).live( 'click', function() {
				$( '.translate_' + $( this ).attr( 'data' ) ).toggle( 'slow' );
				$( '#content_' + $( this ).attr( 'data' ) + '_ifr' ).height( '400px' );
			} );
		}
	};
	// Kick-Off
	$( document ).ready( function( $ ) { advanced_translator.init(); } );
} )( jQuery );


// relationship control
(function ($) {

	$.fn.mlp_search = function (options) {

		var settings = $.extend(
				{
					// Default values.
					remote_blog_id:   this.data('remote_blog_id'),
					remote_post_id:   this.data('remote_post_id'),
					source_blog_id:   this.data('source_blog_id'),
					source_post_id:   this.data('source_post_id'),
					// the selectors to listen on
					search_field:     'input.mlp_search_field',
					result_container: 'ul.mlp_search_results',
					action:           'mlp_search',
					nonce:            '',
					spinner:          '<span class="spinner no-float" style="display:block;"></span>'
				},
				options
			),


			original_content = $(settings.result_container).html(),
			search_field     = $(settings.search_field),
			stored           = [],

			insert = function (content) {
				$(settings.result_container).html(content);
			},

			fetch = function (keywords) {

				if (stored[ keywords ]) {
					insert(stored[ keywords ]);
					return;
				}

				insert(settings.spinner);

				var ajax = $.post(
					ajaxurl,
					{
						action:         settings.action,
						source_post_id: settings.source_post_id,
						source_blog_id: settings.source_blog_id,
						remote_post_id: settings.remote_post_id,
						remote_blog_id: settings.remote_blog_id,
						s:              keywords
					}
				);

				ajax.done(function (data) {
					stored[ keywords ] = data;
					insert(data);
				});
			};


		// prevent submission by enter key
		search_field.keypress(function (event) {
			if (event.which == 13)
				return false;
		});

		search_field.on('keyup', function (event) {
			event.preventDefault();
			event.stopPropagation();

			var str = $.trim(this.value);

			if (!str || 0 == str.length)
				insert(original_content);

			if (2 < str.length) {
				fetch(str);
			}
		});
	};
})(jQuery);

(function ($) {
	$('.mlp_rsc_save_reload').on('click.mlp', function (event) {
		event.stopPropagation();
		event.preventDefault();

		var source_post_id = $(this).data('source_post_id'),
			source_blog_id = $(this).data('source_blog_id'),
			remote_post_id = $(this).data('remote_post_id'),
			remote_blog_id = $(this).data('remote_blog_id'),
			current_value = $('input[name="mlp_rsc_action[' + remote_blog_id + ']"]:checked').val(),
			new_post_id = 0,
			new_post_title = '',

			disconnect = function () {
				change_relationship('disconnect');
			},

			new_relation = function () {
				new_post_title = $('input[name="post_title"]').val();
				change_relationship('new_relation');
			},

			connect_existing = function () {

				new_post_id = $('input[name="mlp_add_post[' + remote_blog_id + ']"]:checked').val();

				if (!new_post_id || 0 == new_post_id)
					alert('Please select a post.');
				else
					change_relationship('connect_existing');
			},

			ajax_success = function (data, textStatus, jqXHR) {
				console.log('ajax_success', {
					as_data:       data,
					as_textStatus: textStatus,
					as_jqXHR:      jqXHR
				});
				// reload to populate the editor with the new data
				window.location.reload(true);
			},

			change_relationship = function (action) {

				var data =
				{
					action:         'mlp_rsc_' + action,
					source_post_id: source_post_id,
					source_blog_id: source_blog_id,
					remote_post_id: remote_post_id,
					remote_blog_id: remote_blog_id,
					new_post_id:    new_post_id,
					new_post_title: new_post_title
				};

				$.ajax({
					type:    "POST",
					url:     ajaxurl,
					data:    data,
					success: ajax_success,
					async:   false
				});
			};

		if (!current_value || 'stay' == current_value)
			return;

		if ('disconnect' == current_value)
			disconnect();

		if ('new' == current_value)
			new_relation();

		if ('search' == current_value)
			connect_existing();
	});
})(jQuery);

/**
 * Handle the custom post type nav menu meta box
 */
jQuery( document ).ready( function($) {
	$( '#submit-mlp_language' ).click( function( event ) {
		event.preventDefault();

		var items      = $( '#' + mlp_nav_menu.metabox_list_id + ' li :checked' ),
			submit     = $( '#submit-mlp_language' ),
			languages  = [],
			post_data  = { action: mlp_nav_menu.action },
			menu_id    = $( '#menu' ).val();

		items.each( function() {
			languages.push( $( this ).val() );
		} );

		// Show spinner
		$( '#' + mlp_nav_menu.metabox_id ).find('.spinner').show();

		// Disable button
		submit.prop( 'disabled', true );

		post_data[ "mlp_sites" ]             = languages;
		post_data[ mlp_nav_menu.nonce_name ] = mlp_nav_menu.nonce;
		post_data[ "menu" ] = menu_id;

		// Send checked post types with our action, and nonce
		$.post( mlp_nav_menu.ajaxurl, post_data,

			// AJAX returns html to add to the menu, hide spinner, remove checks
			function( response ) {
				$( '#menu-to-edit' ).append( response );
				$( '#' + mlp_nav_menu.metabox_id ).find('.spinner').hide();
				items.prop( "checked", false);
				submit.prop( "disabled", false );
			}
		);
	});
});

/**
 * Quicklink
 */
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