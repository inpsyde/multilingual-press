const $ = window.jQuery;

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
		 * Array holding the default search result HTML strings.
		 * @type {string[]}
		 */
		this.defaultResults = [];

		/**
		 * Array holding jQuery objects representing the search result containers.
		 * @type {jQuery[]}
		 */
		this.resultsContainers = [];

		/**
		 * The module settings.
		 * @type {Object}
		 */
		this.moduleSettings = options.moduleSettings;

		/**
		 * Minimum number of characters required to fire the remote post search.
		 * @type {number}
		 */
		this.searchThreshold = parseInt( this.moduleSettings.searchThreshold, 10 );

		/**
		 * The model object.
		 * @type {Model}
		 */
		this.model = options.model;
		this.listenTo( this.model, 'change', this.render );
	}

	/**
	 * Initializes both the default search result view as well as the result container for the given element.
	 * @param {Element} element - The HTML element.
	 */
	initializeResult( element ) {
		const $element = $( element ),
			$resultsContainer = $( '#' + $element.data( 'results-container-id' ) ),
			siteID = $element.data( 'remote-site-id' );

		this.defaultResults[ siteID ] = $resultsContainer.html();
		this.resultsContainers[ siteID ] = $resultsContainer;
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
			this.resultsContainers[ remoteSiteID ].html( this.defaultResults[ remoteSiteID ] );
		} else if ( value.length >= this.searchThreshold ) {
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
					processData: true
				} );
			}, 400 );
		}
	}

	/**
	 * Renders the found posts to the according results container.
	 * @returns {boolean} Whether or not new data has been rendered.
	 */
	render() {
		let data;

		if ( this.model.get( 'success' ) ) {
			data = this.model.get( 'data' );
			this.resultsContainers[ data.remoteSiteID ].html( data.html );

			return true;
		}

		return false;
	}
}

export default RemotePostSearch;
