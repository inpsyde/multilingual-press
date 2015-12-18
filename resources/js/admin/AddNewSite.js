/* global MultilingualPress */
(function( $ ) {
	'use strict';

	var AddNewSite = Backbone.View.extend( {

		// This is suboptimal, yes, but Core gives us no choice here. :(
		el: '#wpbody-content form',

		events: {
			'change #site-language': 'adaptLanguage',
			'change #inpsyde_multilingual_based': 'togglePluginsRow'
		},

		/**
		 * Initializes the TermTranslator module.
		 */
		initialize: function() {

			// TODO: Template stuff...

			// As soon as the additional admin page markup is injected via a template, setTimeout can be removed.
			setTimeout( function() {
				this.$language = $( '#inpsyde_multilingual_lang' );
				this.$pluginsRow = $( '#blog_activate_plugins' ).closest( 'tr' );
			}.bind( this ), 100 );
		},

		/**
		 * Sets MultilingualPress's language select to the currently selected site language.
		 * @param {Event} event - The change event of the site language select element.
		 */
		adaptLanguage: function( event ) {
			var language = this.getSiteLanguage( $( event.currentTarget ) );
			if ( this.$language.find( '[value="' + language + '"]' ).length ) {
				this.$language.val( language );
			}
		},

		/**
		 * Returns the selected site language.
		 * @param {Object} $select - The site language select element.
		 * @returns {string} - The selected site language.
		 */
		getSiteLanguage: function( $select ) {
			var siteLanguage = $select.val();
			if ( siteLanguage ) {
				return siteLanguage.replace( '_', '-' );
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

	MultilingualPress.registerModule( 'network/site-new.php', 'AddNewSite', AddNewSite );
})( jQuery );
