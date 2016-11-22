const { jQuery: $ } = window;

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
 * The MultilingualPress LiveSearch module.
 */
class LiveSearch extends Backbone.View {
	/**
	 * Constructor. Sets up the properties.
	 * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
	 */
	constructor( options = {} ) {
		super( options );

		/**
		 * Action to be used in search requests.
		 * @type {String}
		 */
		_this.action = String( options.settings.action || '' );

		/**
		 * Argument name to be used in order to denote the search in requests.
		 * @type {String}
		 */
		_this.argName = String( options.settings.argName || '' );

		/**
		 * The settings.
		 * @type {Object}
		 */
		_this.settings = options.settings;

		/**
		 * Minimum number of characters required to fire the live search.
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
		const $container = $( element ).closest( '.mlp-relationship-control' );
		const selector = $container.data( 'results-selector' ) || '';
		const $resultsContainer = $( selector );
		const siteId = $container.data( 'remote-site-id' );

		_this.defaultResults[ siteId ] = $resultsContainer.html();
		_this.resultsContainers[ siteId ] = $resultsContainer;
	}

	/**
	 * Initializes both the default search result views as well as the result containers.
	 */
	initializeResults() {
		$( '.mlp-rc-search' ).each( ( index, element ) => this.initializeResult( element ) );
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
		const $input = $( event.target );
		const value = $.trim( $input.val() || '' );

		if ( value === $input.data( 'value' ) ) {
			return;
		}

		clearTimeout( this.reactToInputTimer );

		$input.data( 'value', value );

		if ( '' === value ) {
			const remoteSiteId = $input.closest( '.mlp-relationship-control' ).data( 'remote-site-id' );

			_this.resultsContainers[ remoteSiteId ].html( _this.defaultResults[ remoteSiteId ] );
		} else if ( value.length >= _this.threshold ) {
			const $container = $input.closest( '.mlp-relationship-control' );

			this.reactToInputTimer = setTimeout( () => {
				this.model.fetch( {
					data: {
						action: _this.action,
						remote_post_id: $container.data( 'remote-post-id' ),
						remote_site_id: $container.data( 'remote-site-id' ),
						source_post_id: $container.data( 'source-post-id' ),
						source_site_id: $container.data( 'source-site-id' ),
						[ _this.argName ]: value
					},
					processData: true
				} );
			}, 400 );
		}
	}

	/**
	 * Renders the found posts to the according results container.
	 * @returns {Boolean} Whether or not new data has been rendered.
	 */
	render() {
		if ( this.model.get( 'success' ) ) {
			const data = this.model.get( 'data' );

			if ( _this.resultsContainers[ data.remoteSiteId ] ) {
				_this.resultsContainers[ data.remoteSiteId ].html( data.html );
			}

			return true;
		}

		return false;
	}
}

export default LiveSearch;
