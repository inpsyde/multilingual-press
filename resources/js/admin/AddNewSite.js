/* global MultilingualPress */
(function( $ ) {
	'use strict';

	/**
	 * Constructor for the MultilingualPress AddNewSite module.
	 * @constructor
	 */
	var AddNewSite = Backbone.View.extend( {
		el: '#wpbody-content form',

		events: {
			'change #site-language': 'adaptLanguage',
			'change #mlp-base-site-id': 'togglePluginsRow'
		},

		/**
		 * Initializes the AddNewSite module.
		 */
		initialize: function() {

			// TODO: Template stuff...

			// As soon as the additional admin page markup is injected via a template, setTimeout can be removed.
			setTimeout( function() {
				this.$language = $( '#mlp-site-language' );
				this.$pluginsRow = $( '#mlp-activate-plugins' ).closest( 'tr' );
			}.bind( this ), 100 );
		},

		/**
		 * Sets MultilingualPress's language select to the currently selected site language.
		 * @param {Event} event - The change event of the site language select element.
		 */
		adaptLanguage: function( event ) {
			var language = this.getLanguage( $( event.currentTarget ) );
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
			this.$pluginsRow.toggle( 0 < $( event.currentTarget ).val() );
		}
	} );

	// Register the AddNewSite module for the Add New Site network admin page.
	MultilingualPress.registerModule( 'network/site-new.php', 'AddNewSite', AddNewSite );
})( jQuery );
