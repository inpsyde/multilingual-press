/* global MultilingualPress */
(function( $ ) {
	'use strict';

	var AddNewSite = Backbone.View.extend( /** @lends AddNewSite# */ {
		/**
		 * @type {string}
		 */
		el: '#wpbody-content form',
		/**
		 * @type {Object}
		 */
		events: {
			'change #site-language': 'adaptLanguage',
			'change #mlp-base-site-id': 'togglePluginsRow'
		},

		template: _.template( $( '#mlp-add-new-site-template' ).html() || '' ),

		/**
		 * Initializes the AddNewSite module.
		 *
		 * @classdesc Constructor for the MultilingualPress AddNewSite module.
		 *
		 * @extends Backbone.View
		 * @constructs
		 * @name AddNewSite
		 */
		initialize: function() {

			// First render the template, ...
			this.render();

			// ...then set up the properties using elements that just have been injected into the DOM.
			this.$language = $( '#mlp-site-language' );

			this.$pluginsRow = $( '#mlp-activate-plugins' ).closest( 'tr' );
		},

		/**
		 * Renders the MultilingualPress table markup.
		 */
		render: function() {
			this.$el.find( '.submit' ).before( this.template() );
		},

		/**
		 * Sets MultilingualPress's language select to the currently selected site language.
		 * @param {Event} event - The change event of the site language select element.
		 */
		adaptLanguage: function( event ) {
			var language = this.getLanguage( $( event.target ) );
			if ( this.$language.find( '[value="' + language + '"]' ).length ) {
				this.$language.val( language );
			}
		},

		/**
		 * Returns the selected language of the given select element.
		 * @param {Object} $select - A select element.
		 * @returns {string} - The selected language.
		 */
		getLanguage: function( $select ) {
			var language = $select.val();
			if ( language ) {
				return language.replace( '_', '-' );
			}

			return 'en-US';
		},

		/**
		 * Toggles the Plugins row according to the source site ID select element's value.
		 * @param {Event} event - The change event of the source site ID select element.
		 */
		togglePluginsRow: function( event ) {
			this.$pluginsRow.toggle( 0 < $( event.target ).val() );
		}
	} );

	// Register the AddNewSite module for the Add New Site network admin page.
	MultilingualPress.registerModule( 'network/site-new.php', 'AddNewSite', AddNewSite );
})( jQuery );
