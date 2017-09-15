const $ = window.jQuery;

// Internal pseudo-namespace for private data.
// NOTE: _this is shared between ALL instances of this module! So far, there is only one instance, so no problem NOW.
const _this = {
	/**
	 * Array holding the default search result HTML strings.
	 * @type {String[]}
	 */
	defaultResults: [],

	/**
	 * Array holding jQuery objects representing the search result containers.
	 * @type {jQuery[]}
	 */
	resultsContainers: []
};

/**
 * The MultilingualPress RemotePostSearch module.
 */
class RemotePostSearch extends Backbone.View {
	/**
	 * Constructor. Sets up the properties.
	 * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
	 */
	constructor( options = {} ) {
		super( options );

		/**
		 * The settings.
		 * @type {Object}
		 */
		_this.settings = options.settings;

		/**
		 * Minimum number of characters required to fire the remote post search.
		 * @type {Number}
		 */
		_this.threshold = parseInt( options.settings.threshold, 10 ) || 3;

		this.listenTo( this.model, 'change', this.render );
	}

	/**
	 * Returns the settings.
	 * @returns {Object} The settings.
	 */
	get settings() {
		return _this.settings;
	}

	/**
	 * Initializes both the default search result view as well as the result container for the given element.
	 * @param {HTMLElement} element - The HTML element.
	 */
	initializeResult( element ) {
		const $element = $( element ),
			$resultsContainer = $( '#' + $element.data( 'results-container-id' ) ),
			siteID = $element.data( 'remote-site-id' );

		_this.defaultResults[ siteID ] = $resultsContainer.html();
		_this.resultsContainers[ siteID ] = $resultsContainer;
	}

	/**
	 * Initializes both the default search result views as well as the result containers.
	 */
	initializeResults() {
		$( '.mlp-search-field' ).each( ( index, element ) => this.initializeResult( element ) );
	}

	/**
	 * Prevents form submission due to the enter key being pressed.
	 * @param {Event} event - The keydown event of a post search element.
	 */
	preventFormSubmission( event ) {
		if ( 13 === event.which ) {
			event.preventDefault();
		}
	}

	/**
	 * According to the user input, either search for posts, or display the initial post selection.
	 * @param {Event} event - The keyup event of a post search element.
	 */
	reactToInput( event ) {
		const $input = $( event.target ),
			value = $.trim( $input.val() || '' );

		let remoteSiteID;

		if ( value === $input.data( 'value' ) ) {
			return;
		}

		clearTimeout( this.reactToInputTimer );

		$input.data( 'value', value );

		remoteSiteID = $input.data( 'remote-site-id' );

		if ( '' === value ) {
			_this.resultsContainers[ remoteSiteID ].html( _this.defaultResults[ remoteSiteID ] );
		} else if ( value.length >= _this.threshold ) {
			this.reactToInputTimer = setTimeout( () => {
				this.model.fetch( {
					data: {
						action: 'mlp_rc_remote_post_search',
						remote_site_id: remoteSiteID,
						remote_post_id: $input.data( 'remote-post-id' ),
						source_site_id: $input.data( 'source-site-id' ),
						source_post_id: $input.data( 'source-post-id' ),
						s: value
					},
					processData: true,
					type: 'POST'
				} );
			}, 400 );
		}
	}

	/**
	 * Renders the found posts to the according results container.
	 * @returns {Boolean} Whether or not new data has been rendered.
	 */
	render() {
		let data;

		if ( this.model.get( 'success' ) ) {
			data = this.model.get( 'data' );

			if ( _this.resultsContainers[ data.remoteSiteID ] ) {
				_this.resultsContainers[ data.remoteSiteID ].html( data.html );
			}

			return true;
		}

		return false;
	}
}

export default RemotePostSearch;
