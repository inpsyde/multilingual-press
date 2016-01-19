/* global MultilingualPress */
(function( $ ) {
	'use strict';

	/**
	 * Settings for the MultilingualPress RCPostSearch module. Only available on the targeted admin pages.
	 * @type {Object}
	 */
	var moduleSettings = MultilingualPress.getSettings( 'RelationshipControl' );

	/**
	 * Constructor for the MultilingualPress RCPostSearchResult model.
	 * @constructor
	 */
	var RCPostSearchResult = Backbone.Model.extend( {
		urlRoot: moduleSettings.ajaxURL
	} );

	/**
	 * Constructor for the MultilingualPress RCPostSearch module.
	 * @constructor
	 */
	var RCPostSearch = Backbone.View.extend( {
		el: 'body',

		events: {
			'keydown input.mlp_search_field': 'preventFormSubmission',
			'keyup input.mlp_search_field': 'reactToInput'
		},

		/**
		 * Initializes the RCPostSearch module.
		 */
		initialize: function() {
			this.defaultResults = [];
			this.resultsContainers = [];

			this.model = new RCPostSearchResult();
			this.listenTo( this.model, 'change', this.render );

			$( '.mlp_search_field' ).each( function( index, element ) {
				var $element = $( element ),
					siteID = $element.data( 'remote-site-id' ),
					$resultsContainer = $( '#' + $element.data( 'results-container-id' ) );

				this.defaultResults[ siteID ] = $resultsContainer.html();
				this.resultsContainers[ siteID ] = $resultsContainer;
			}.bind( this ) );
		},

		/**
		 * Prevents form submission due to the enter key being pressed.
		 * @param {Event} event - The keydown event of a post search element.
		 */
		preventFormSubmission: function( event ) {
			if ( 13 === event.which ) {
				event.preventDefault();
			}
		},

		/**
		 * According to the user input, either search for posts, or display the initial post selection.
		 * @param {Event} event - The keyup event of a post search element.
		 */
		reactToInput: function( event ) {
			var $input = $( event.target ),
				remoteSiteID = $input.data( 'remote-site-id' ),
				value = $.trim( $input.val() || '' );

			if ( value === $input.data( 'value' ) ) {
				return;
			}

			clearTimeout( this.reactToInputTimer );

			$input.data( 'value', value );

			if ( '' === value ) {
				this.resultsContainers[ remoteSiteID ].html( this.defaultResults[ remoteSiteID ] );
			} else if ( 2 < value.length ) {
				this.reactToInputTimer = setTimeout( function() {
					this.model.fetch( {
						data: {
							action: 'mlp_rsc_search',
							remote_blog_id: remoteSiteID,
							remote_post_id: $input.data( 'remote-post-id' ),
							source_blog_id: $input.data( 'source-site-id' ),
							source_post_id: $input.data( 'source-post-id' ),
							s: value
						},
						processData: true
					} );
				}.bind( this ), 400 );
			}
		},

		/**
		 * Renders the found posts to the according results container.
		 */
		render: function() {
			var data = this.model.get( 'data' );
			if ( this.model.get( 'success' ) ) {
				this.resultsContainers[ data.remoteSiteID ].html( data.html );
			}
		}
	} );

	// Register the RCPostSearch module for the Add New Post and the Edit Post admin pages.
	MultilingualPress.registerModule( [ 'post.php', 'post-new.php' ], 'RCPostSearch', RCPostSearch );
})( jQuery );
