/* global MultilingualPress */
(function( $ ) {
	'use strict';

	/**
	 * Constructor for the MultilingualPress Common module.
	 * @class
	 */
	var Common = Backbone.View.extend( {
		/** @lends Common.prototype */

		el: 'body',

		events: {
			'click .mlp-click-toggler': 'toggleElement'
		},

		/**
		 * Initializes the Common module.
		 *
		 * @augments Backbone.View
		 * @constructs
		 * @name Common
		 */
		initialize: function() {
			this.initializeStateTogglers();
		},

		/**
		 * Initializes the togglers that work by using their individual state.
		 */
		initializeStateTogglers: function() {
			$( '.mlp-state-toggler' ).each( function( index, element ) {
				var $toggler = $( element );
				$( '[name="' + $toggler.attr( 'name' ) + '"]' ).on( 'change', {
					$toggler: $toggler
				}, this.toggleElementIfChecked );
			}.bind( this ) );
		},

		/**
		 * Toggles the element with the ID given in the according toggler's data attribute if the toggler is checked.
		 * @param {Event} event - The change event of an input element.
		 */
		toggleElementIfChecked: function( event ) {
			var $toggler = event.data.$toggler,
				targetID = $toggler.data( 'toggle-target' );
			if ( targetID ) {
				$( targetID ).toggle( $toggler.is( ':checked' ) );
			}
		},

		/**
		 * Toggles the element with the ID given in the according data attribute.
		 * @param {Event} event - The click event of a toggler element.
		 */
		toggleElement: function( event ) {
			var targetID = $( event.target ).data( 'toggle-target' ) || '';
			if ( targetID ) {
				$( targetID ).toggle();
			}
		}
	} );

	/**
	 * Register the Common module for all admin pages.
	 *
	 * @memberof MultilingualPress.Modules
 	 */
	MultilingualPress.Modules.Common = new Common();
})( jQuery );
