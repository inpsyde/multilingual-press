/* global MultilingualPress */
(function( $ ) {
	'use strict';

	var AddNewSite = {
		initialize: function() {
			AddNewSite.initializeLanguages();

			AddNewSite.initializePlugins();
		},

		initializeLanguages: function() {
			AddNewSite.$language = $( '#inpsyde_multilingual_lang' );

			AddNewSite.$siteLanguage = $( '#site-language' );
			if ( AddNewSite.$siteLanguage.length ) {
				AddNewSite.$siteLanguage.on( 'change', AddNewSite.adaptLanguage );
			}
		},

		adaptLanguage: function() {
			var language = AddNewSite.getLanguage();
			if ( AddNewSite.$language.find( '[value="' + language + '"]' ).length ) {
				AddNewSite.$language.val( language );
			}
		},

		getLanguage: function() {
			var language = AddNewSite.$siteLanguage.val();
			if ( ! language ) {
				return 'en-US';
			}

			return language.replace( '_', '-' );
		},

		initializePlugins: function() {
			AddNewSite.$sourceSiteID = $( '#inpsyde_multilingual_based' );

			AddNewSite.$pluginsRow = $( '#blog_activate_plugins' ).closest( 'tr' );

			if ( AddNewSite.$sourceSiteID.length && AddNewSite.$pluginsRow.length ) {
				AddNewSite.$sourceSiteID.on( 'change', AddNewSite.togglePluginsRow );
			}
		},

		togglePluginsRow: function() {
			AddNewSite.$pluginsRow.toggle( 0 < $( this ).val() );
		}
	};

	MultilingualPress.AddNewSite = AddNewSite;

	$( function() {

		// Parts of the Add New Site network admin page HTML are injected via JavaScript, so let's wait a while.
		setTimeout( MultilingualPress.AddNewSite.initialize, 100 );
	} );
})( jQuery );
