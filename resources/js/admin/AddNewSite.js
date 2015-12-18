/* global MultilingualPress */
(function( $ ) {
	'use strict';

	var AddNewSite = {

		/**
		 * Initializes the AddNewSite module.
		 */
		initialize: function() {

			AddNewSite.initializeLanguages();

			AddNewSite.initializePlugins();
		},

		/**
		 * Initializes the Languages part.
		 */
		initializeLanguages: function() {

			AddNewSite.$language = $( '#inpsyde_multilingual_lang' );

			AddNewSite.$siteLanguage = $( '#site-language' );
			if ( AddNewSite.$siteLanguage.length ) {
				AddNewSite.$siteLanguage.on( 'change', function() {

					AddNewSite.adaptLanguage();
				} );
			}
		},

		/**
		 * Sets MultilingualPress's language select to the currently selected site language.
		 */
		adaptLanguage: function() {

			var language = AddNewSite.getSiteLanguage();
			if ( AddNewSite.$language.find( '[value="' + language + '"]' ).length ) {
				AddNewSite.$language.val( language );
			}
		},

		/**
		 * Returns the selected site language.
		 * @returns {string} - The selected site language.
		 */
		getSiteLanguage: function() {

			var siteLanguage = AddNewSite.$siteLanguage.val();
			if ( siteLanguage ) {
				return siteLanguage.replace( '_', '-' );
			}

			return 'en-US';
		},

		/**
		 * Initializes the Plugins part.
		 */
		initializePlugins: function() {

			AddNewSite.$sourceSiteID = $( '#inpsyde_multilingual_based' );

			AddNewSite.$pluginsRow = $( '#blog_activate_plugins' ).closest( 'tr' );

			if ( AddNewSite.$sourceSiteID.length && AddNewSite.$pluginsRow.length ) {
				AddNewSite.$sourceSiteID.on( 'change', function() {

					AddNewSite.togglePluginsRow( $( this ) );
				} );
			}
		},

		/**
		 * Toggles the Plugins row according to the given select element's value.
		 * @param {Object} $select - The select element.
		 */
		togglePluginsRow: function( $select ) {

			AddNewSite.$pluginsRow.toggle( 0 < $select.val() );
		}
	};

	MultilingualPress.AddNewSite = AddNewSite;

	$( function() {

		// Parts of the Add New Site network admin page HTML are injected via JavaScript, so let's wait a while.
		setTimeout( MultilingualPress.AddNewSite.initialize, 100 );
	} );
})( jQuery );
