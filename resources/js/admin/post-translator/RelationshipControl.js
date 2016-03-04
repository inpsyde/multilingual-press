/* global ajaxurl */
(function( $, MultilingualPress ) {
	'use strict';

	/**
	 * Settings for the MultilingualPress RelationshipControl module. Only available on the targeted admin pages.
	 * @type {Object}
	 */
	var moduleSettings = MultilingualPress.getSettings( 'RelationshipControl' );

	/**
	 * @class RelationshipControl
	 * @classdesc MultilingualPress RelationshipControl module.
	 * @extends Backbone.View
	 */
	var RelationshipControl = Backbone.View.extend( /** @lends RelationshipControl# */ {
		/**
		 * Initializes the RelationshipControl module.
		 */
		initialize: function() {
			this.unsavedRelationships = [];

			this.initializeEventHandlers();
		},

		/**
		 * Initializes the event handlers for all custom relationship control events.
		 */
		initializeEventHandlers: function() {
			MultilingualPress.Events.on( {
				'RelationshipControl:connectExistingPost': this.connectExistingPost,
				'RelationshipControl:connectNewPost': this.connectNewPost,
				'RelationshipControl:disconnectPost': this.disconnectPost
			}, this );
		},

		/**
		 * Updates the unsaved relationships array for the meta box containing the changed radio input element.
		 * @param {Event} event - The change event of a radio input element.
		 */
		updateUnsavedRelationships: function( event ) {
			var $input = $( event.target ),
				$metaBox = $input.closest( '.mlp-translation-meta-box' ),
				$button = $metaBox.find( '.mlp-save-relationship-button' ),
				index = this.findMetaBox( $metaBox );

			if ( 'stay' === $input.val() ) {
				$button.prop( 'disabled', 'disabled' );

				if ( -1 !== index ) {
					this.unsavedRelationships.splice( index, 1 );
				}
			} else if ( -1 === index ) {
				this.unsavedRelationships.push( $metaBox );

				$button.removeAttr( 'disabled' );
			}
		},

		/**
		 * Returns the index of the given meta box in the unsaved relationships array, and -1 if not found.
		 * @param {Object} $metaBox - The meta box element.
		 * @returns {Number} The index of the meta box.
		 */
		findMetaBox: function( $metaBox ) {
			$.each( this.unsavedRelationships, function( index, element ) {
				if ( element === $metaBox ) {
					return index;
				}
			} );

			return -1;
		},

		/**
		 * Displays a confirm dialog informing the user about unsaved relationships, if any.
		 * @param {Event} event - The click event of the publish button.
		 */
		confirmUnsavedRelationships: function( event ) {
			if ( this.unsavedRelationships.length && ! window.confirm( moduleSettings.L10n.unsavedRelationships ) ) {
				event.preventDefault();
			}
		},

		/**
		 * Triggers the according event in case of changed relationships.
		 * @param {Event} event - The click event of a save relationship button.
		 */
		saveRelationship: function( event ) {
			var $button = $( event.target ),
				remoteSiteID = $button.data( 'remote-site-id' ),
				action = $( 'input[name="mlp-rc-action[' + remoteSiteID + ']"]:checked' ).val(),
				eventName = this.getEventName( action );

			if ( 'stay' === action ) {
				return;
			}

			$button.prop( 'disabled', 'disabled' );

			/**
			 * Triggers the according event for the current relationship action, and passes data and the event's name.
			 */
			MultilingualPress.Events.trigger( 'RelationshipControl:' + eventName, {
				action: 'mlp_rc_' + action,
				remote_site_id: remoteSiteID,
				remote_post_id: $button.data( 'remote-post-id' ),
				source_site_id: $button.data( 'source-site-id' ),
				source_post_id: $button.data( 'source-post-id' )
			}, eventName );
		},

		/**
		 * Returns the according event name for the given relationship action.
		 * @param {String} action - A relationship action.
		 * @returns {String} The event name.
		 */
		getEventName: function( action ) {
			switch ( action ) {
				case 'search':
					return 'connectExistingPost';

				case 'new':
					return 'connectNewPost';

				case 'disconnect':
					return 'disconnectPost';

				default:
					return '';
			}
		},

		/**
		 * Handles changing a post's relationship by connecting a new post.
		 * @param {Object} data - The common data for all relationship requests.
		 */
		connectNewPost: function( data ) {
			data.new_post_title = $( 'input[name="post_title"]' ).val();

			this.sendRequest( data );
		},

		/**
		 * Handles changing a post's relationship by disconnecting the currently connected post.
		 * @param {Object} data - The common data for all relationship requests.
		 */
		disconnectPost: function( data ) {
			this.sendRequest( data );
		},

		/**
		 * Handles changing a post's relationship by connecting an existing post.
		 * @param {Object} data - The common data for all relationship requests.
		 */
		connectExistingPost: function( data ) {
			var newPostID = $( 'input[name="mlp_add_post[' + data.remote_site_id + ']"]:checked' ).val() || 0;
			if ( newPostID ) {
				data.new_post_id = Number( newPostID );

				this.sendRequest( data );
			} else {
				window.alert( moduleSettings.L10n.noPostSelected );
			}
		},

		/**
		 * Changes a post's relationhip by sending a synchronous AJAX request with the according new relationship data.
		 * @param {Object} data - The relationship data.
		 */
		sendRequest: function( data ) {
			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: data,
				success: this.reloadLocation,
				async: false
			} );
		},

		/**
		 * Reloads the current page.
		 */
		reloadLocation: function() {
			window.location.reload( true );
		}
	} );

	// Register the RelationshipControl module for the Add New Post and the Edit Post admin pages.
	MultilingualPress.registerModule( [ 'post.php', 'post-new.php' ], 'RelationshipControl', RelationshipControl, {
		el: 'body',
		events: {
			'change .mlp-rc-actions input': 'updateUnsavedRelationships',
			'click #publish': 'confirmUnsavedRelationships',
			'click .mlp-save-relationship-button': 'saveRelationship'
		}
	} );
})( jQuery, window.MultilingualPress );
