// Internal pseudo-namespace for private data.
// NOTE: _this is shared between ALL instances of this module! So far, there is only one instance, so no problem NOW.
const _this = {};

/**
 * The MultilingualPress Redirector module.
 */
class Redirector {
	/**
	 * Constructor. Sets up the properties.
	 * @param {Object} settings - The settings.
	 * @param {Object} Util - The set of utility methods.
	 */
	constructor( settings, Util ) {
		/**
		 * The settings.
		 * @type {Object}
		 */
		_this.settings = settings;

		/**
		 * The storage name.
		 * @type {Object}
		 */
		_this.storageName = 'mlpNoredirectStorage';

		/**
		 * The preferred languages of the user.
		 * @type {String[]}
		 */
		_this.userLanguages = navigator.languages || [];

		/**
		 * The set of utility methods.
		 * @type {Object}
		 */
		_this.Util = Util;
	}

	/**
	 * Initializes the module.
	 */
	initialize() {
		const noredirect = this.getNoredirectLanguage();
		if ( noredirect ) {
			this.storeLanguage( noredirect );

			return;
		}

		const contentLanguage = this.findContentLanguage();
		if ( contentLanguage ) {
			this.redirect( contentLanguage );
		}
	}

	/**
	 * Returns the best-matching content language, if any.
	 * @return {String} The best-matching content language.
	 */
	findContentLanguage() {
		if ( _this.userLanguages.length && Object.keys( _this.settings.urls ).length ) {
			let contentLanguage;

			for ( const language of _this.userLanguages ) {
				contentLanguage = this.matchLanguage( language );
				if ( contentLanguage ) {
					return contentLanguage;
				}
			}

			for ( const language of this.getAdditionalUserLanguages() ) {
				contentLanguage = this.matchLanguage( language );
				if ( contentLanguage ) {
					return contentLanguage;
				}
			}
		}

		return '';
	}

	/**
	 * Returns the regionless languages of the user that have not been defined before.
	 * @returns {String[]} The regionless languages of the user not defined before.
	 */
	getAdditionalUserLanguages() {
		const userLanguages = _this.userLanguages;

		return userLanguages.reduce( ( languages, language ) => {
			const index = language.indexOf( '-' );
			if ( 0 < index ) {
				language = language.substr( 0, index );
			}

			if ( ! userLanguages.includes( language ) ) {
				languages.push( language );
			}

			return languages;
		}, [] );
	}

	/**
	 * Returns the noredirect language included in the request, if any.
	 * @returns {String} Language.
	 */
	getNoredirectLanguage() {
		const value = ( new RegExp( `[\\?&]${_this.settings.noredirectKey}=([^?&#]*)` ) ).exec( window.location.href );

		return value ? decodeURIComponent( value[ 1 ].replace( /\+/g, ' ' ) ) : '';
	}

	/**
	 * Returns the stored languages.
	 * @returns {String[]} The stored languages.
	 */
	getStoredLanguages() {
		const languages = localStorage.getItem( _this.storageName );

		return languages ? languages.split( ' ' ) : [];
	}

	/**
	 * Returns the best-matching content language for the given user language.
	 * @param {String} userLanguage - A language of the user.
	 * @param {Boolean} [recursive=true] - Optional. Recursive redirection? Defaults to true.
	 * @return {String} The best-matching content language.
	 */
	matchLanguage( userLanguage, recursive = true ) {
		if ( _this.settings.urls[ userLanguage ] ) {
			return userLanguage;
		}

		const index = userLanguage.indexOf( '-' );
		if ( -1 === index ) {
			const start = `${userLanguage}-`;

			for ( const contentLanguage of Object.keys( _this.settings.urls ) ) {
				if ( contentLanguage.startsWith( start ) ) {
					return contentLanguage;
				}
			}

			return '';
		}

		if ( recursive ) {
			return this.matchLanguage( userLanguage.substr( 0, index ), false );
		}

		return '';
	}

	/**
	 * Returns the given language in the noralized, locale-like form.
	 * @param {String} language - The language.
	 * @return {String} Normalized language.
	 */
	normalizeLanguage( language ) {
		return language.replace( /-/, '_' );
	}

	/**
	 * Redirects to the URL according to the given language.
	 * @param {String} language - A language.
	 */
	redirect( language ) {
		this.storeLanguage( language );

		if ( language === _this.settings.currentLanguage ) {
			return;
		}

		const url = _this.settings.urls[ language ].replace( /\?.*$/, '' );

		_this.Util.setLocation( `${url}?${_this.settings.noredirectKey}=${this.normalizeLanguage( language )}` );
	}

	/**
	 * Stores the given language.
	 * @param {String} language - A language.
	 */
	storeLanguage( language ) {
		language = this.normalizeLanguage( language );

		const languages = this.getStoredLanguages();
		if ( languages.includes( language ) ) {
			return;
		}

		languages.push( language );

		localStorage.setItem( _this.storageName, languages.join( ' ' ) );
	}
}

export default Redirector;
