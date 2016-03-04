/* global ajaxurl */
(function( $, MultilingualPress ) {
	'use strict';

	/**
	 * Settings for the MultilingualPress RemotePostSearch module. Only available on the targeted admin pages.
	 * @type {Object}
	 */
	var moduleSettings = MultilingualPress.getSettings( 'RemotePostSearch' );

	/**
	 * Constructor for the MultilingualPress RemotePostSearchResult model.
	 * @class RemotePostSearchResult
	 * @extends Backbone.Model
	 */
	var RemotePostSearchResult = Backbone.Model.extend( /** @lends RemotePostSearchResult# */ {
		/**
		 * @type {string}
		 */
		urlRoot: ajaxurl
	} );


	var RemotePostSearch = Backbone.View.extend(
		/** @lends RemotePostSearch# */
		{
		/**
		 * DOM selector of this View
		 * @type {string}
		 */
		el: 'body',
		/**
		 * Events used by this View
		 * @type {Object}
		 */
		events: {
			'keydown .mlp-search-field': 'preventFormSubmission',
			'keyup .mlp-search-field': 'reactToInput'
		},

		/**
		 * Initializes the RemotePostSearch module.
		 * @classdesc Constructor for the MultilingualPress RemotePostSearch module.
		 * @augments Backbone.View
		 * @constructs
		 * @name RemotePostSearch
		 */
		initialize: function() {
			this.defaultResults = [];
			this.resultsContainers = [];

			this.searchThreshold = parseInt( moduleSettings.searchThreshold, 10 );

			this.model = new RemotePostSearchResult();
			this.listenTo( this.model, 'change', this.render );

			$( '.mlp-search-field' ).each( function( index, element ) {
				var $element = $( element ),
					$resultsContainer = $( '#' + $element.data( 'results-container-id' ) ),
					siteID = $element.data( 'remote-site-id' );

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
				remoteSiteID,
				value = $.trim( $input.val() || '' );

			if ( value === $input.data( 'value' ) ) {
				return;
			}

			clearTimeout( this.reactToInputTimer );

			$input.data( 'value', value );

			remoteSiteID = $input.data( 'remote-site-id' );

			if ( '' === value ) {
				this.resultsContainers[ remoteSiteID ].html( this.defaultResults[ remoteSiteID ] );
			} else if ( value.length >= this.searchThreshold ) {
				this.reactToInputTimer = setTimeout( function() {
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
				}.bind( this ), 400 );
			}
		},

		/**
		 * Renders the found posts to the according results container.
		 */
		render: function() {
			var data;
			if ( this.model.get( 'success' ) ) {
				data = this.model.get( 'data' );

				this.resultsContainers[ data.remoteSiteID ].html( data.html );
			}
		}
	} );

	// Register the RemotePostSearch module for the Add New Post and the Edit Post admin pages.
	MultilingualPress.registerModule( [ 'post.php', 'post-new.php' ], 'RemotePostSearch', RemotePostSearch );
})( jQuery, window.MultilingualPress );
