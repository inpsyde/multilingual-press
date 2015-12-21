/* global Backbone, mlpSettings */
(function( $ ) {
	'use strict';

	/**
	 * Constructor for the MultilingualPress router.
	 * @constructor
	 */
	var MultilingualPressRouter = Backbone.Router.extend( {} );

	/**
	 * Constructor for the MultilingualPress admin controller.
	 * @returns {{Modules: Array, registerModule: registerModule, initialize: initialize}}
	 * @constructor
	 */
	var MultilingualPress = function() {
		var Modules = [],
			Router = new MultilingualPressRouter();

		return {
			Modules: Modules,

			/**
			 * Registers a new module with the given Module callback under the given name for the given rout.
			 * @param {string} route - The route for the module.
			 * @param {string} name - The name of the module.
			 * @param {Function} Module - The constructor callback for the module.
			 * @param {Object} [options={}] - Optional. The options for the module. Default to {}.
			 */
			registerModule: function( route, name, Module, options ) {
				if ( _.isFunction( Module ) ) {
					Router.route( route, name, function() {
						Modules[ name ] = new Module( options || {} );
					} );
				}
			},

			/**
			 * Initializes the instance.
			 */
			initialize: function() {
				Backbone.history.start( {
					root: mlpSettings.adminUrl,
					pushState: true,
					hashChange: false
				} );
			}
		};
	};

	/**
	 * The MultilingualPress admin instance.
	 * @type {MultilingualPress}
	 */
	window.MultilingualPress = new MultilingualPress();

	$( window.MultilingualPress.initialize );
})( jQuery );

// TODO: Refactor the following ... mess.
(function( $ ) {
	"use strict";

	var multilingualPress = {

		init: function() {
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
			$( document ).on( 'click', '[data-toggle_selector]', function() {
				if ( 'INPUT' === this.tagName ) {
					return true;
				}

				$( $( this ).data( 'toggle_selector' ) ).toggle();

				return false;
			} );

			$( 'label.mlp_toggler' ).each( function() {
				var $inputs = $( 'input[name="' + $( '#' + $( this ).attr( 'for' ) ).attr( 'name' ) + '"]' ),
					$toggler = $inputs.filter( '[data-toggle_selector]' );

				if ( $toggler.length ) {
					$inputs.on( 'change', function() {
						$( $toggler.data( 'toggle_selector' ) ).toggle( $toggler.is( ':checked' ) );

						return true;
					} );
				}
			} );
		},

		// Copy post buttons next to media buttons
		copyPost: function( blogId ) {
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

})( jQuery );
