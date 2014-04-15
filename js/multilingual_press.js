/**
 * jQuery Library for Multilingual Press
 *
 * @version        2014.03.24
 * @package		mlp
 *
 * @todo		Doc
 */

jQuery.noConflict();
( function( $ ) {

	/**
	 * Class Holder
	 */
	var multilingual_press = {

		/**
		 * Initial Function
		 */
		init : function () {
			// this.do_blog_checkup();
			this.set_toggle();
			this.copy_post();
		},

		/**
		 * Retrieves all responses from the blog checkup
		 *
		 * @since	0.8
		 */
		do_blog_checkup : function() {
			if ( ! '#multilingual_press_checkup' )
				return;

			$( '#multilingual_press_checkup_link' ).live( 'click', function() {
				var data = {
					action: 'checkup_blogs'
				};

				$.ajax( {
    				url: ajaxurl,
    				data: data,
    				async: true,
    				success: function ( response ) {
    					$( '#multilingual_press_checkup' ).append( response ).delay( 250 ).animate( {
    						backgroundColor: 'lightYellow',
						    borderBottomColor: '#E6DB55',
						    borderLeftColor: '#E6DB55',
    						borderRightColor: '#E6DB55',
    						borderTopColor: '#E6DB55'
    					}, 500 ).delay( 3500 ).slideUp();
    				}
    			} );
			} );
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
				event.stopPropagation();
				event.preventDefault();

				// @formatter:off
				var blog_id = $( this ).data( "blog_id" ),
					title   = $( "#title" ).val(),
					content = $( "#content" ).val(),
					prefix  = "mlp_translation_data_" + blog_id,
					mce     = tinyMCE.get( prefix + "_content" );

				if ( title )
					$( "#" + prefix + "_title" ).val( title );

				if ( content ) {
					$( "#" + prefix + "_content" ).val( content );

					if ( mce )
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