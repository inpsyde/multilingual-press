const $ = window.jQuery;

/**
 * The MultilingualPress RelationshipControl module.
 */
class RelationshipControl extends Backbone.View {
	/**
	 * Constructor. Sets up the properties.
	 * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
	 */
	constructor( options = {} ) {
		super( options );

		/**
		 * The event manager object.
		 * @type {EventManager}
		 */
		this.EventManager = options.EventManager;

		/**
		 * The module settings.
		 * @type {Object}
		 */
		this.moduleSettings = options.moduleSettings;

		/**
		 * Array of jQuery objects representing meta boxes with unsaved relationships.
		 * @type {jQuery[]}
		 */
		this.unsavedRelationships = [];

		/**
		 * The set of utility methods.
		 * @type {Object}
		 */
		this.Util = options.util;
	}

	/**
	 * Initializes the event handlers for all custom relationship control events.
	 */
	initializeEventHandlers() {
		this.EventManager.on( {
			'RelationshipControl:connectExistingPost': this.connectExistingPost,
			'RelationshipControl:connectNewPost': this.connectNewPost,
			'RelationshipControl:disconnectPost': this.disconnectPost
		}, this );
	}

	/**
	 * Updates the unsaved relationships array for the meta box containing the changed radio input element.
	 * @param {Event} event - The change event of a radio input element.
	 */
	updateUnsavedRelationships( event ) {
		const $input = $( event.target ),
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
	}

	/**
	 * Returns the index of the given meta box in the unsaved relationships array, and -1 if not found.
	 * @param {jQuery} $metaBox - The meta box element.
	 * @returns {number} The index of the meta box.
	 */
	findMetaBox( $metaBox ) {
		var metaBoxIndex = -1;

		$.each( this.unsavedRelationships, ( index, element ) => {
			if ( element === $metaBox ) {
				metaBoxIndex = index;
			}
		} );

		return metaBoxIndex;
	}

	/**
	 * Displays a confirm dialog informing the user about unsaved relationships, if any.
	 * @param {Event} event - The click event of the publish button.
	 */
	confirmUnsavedRelationships( event ) {
		if ( this.unsavedRelationships.length && ! window.confirm( this.moduleSettings.L10n.unsavedRelationships ) ) {
			event.preventDefault();
		}
	}

	/**
	 * Triggers the according event in case of changed relationships.
	 * @param {Event} event - The click event of a save relationship button.
	 */
	saveRelationship( event ) {
		const $button = $( event.target ),
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
		this.EventManager.trigger( 'RelationshipControl:' + eventName, {
			action: 'mlp_rc_' + action,
			remote_site_id: remoteSiteID,
			remote_post_id: $button.data( 'remote-post-id' ),
			source_site_id: $button.data( 'source-site-id' ),
			source_post_id: $button.data( 'source-post-id' )
		}, eventName );
	}

	/**
	 * Returns the according event name for the given relationship action.
	 * @param {string} action - The relationship action.
	 * @returns {string} The event name.
	 */
	getEventName( action ) {
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
	}

	/**
	 * Handles changing a post's relationship by connecting a new post.
	 * @param {Object} data - The common data for all relationship requests.
	 */
	connectNewPost( data ) {
		data.new_post_title = $( 'input[name="post_title"]' ).val();

		this.sendRequest( data );
	}

	/**
	 * Handles changing a post's relationship by disconnecting the currently connected post.
	 * @param {Object} data - The common data for all relationship requests.
	 */
	disconnectPost( data ) {
		this.sendRequest( data );
	}

	/**
	 * Handles changing a post's relationship by connecting an existing post.
	 * @param {Object} data - The common data for all relationship requests.
	 * @returns {boolean} Whether or not the request has been sent.
	 */
	connectExistingPost( data ) {
		const newPostID = Number( $( 'input[name="mlp_add_post[' + data.remote_site_id + ']"]:checked' ).val() );

		if ( ! newPostID ) {
			window.alert( this.moduleSettings.L10n.noPostSelected );

			return false;
		}

		data.new_post_id = Number( newPostID );

		this.sendRequest( data );

		return true;
	}

	/**
	 * Changes a post's relationhip by sending a synchronous AJAX request with the according new relationship data.
	 * @param {Object} data - The relationship data.
	 */
	sendRequest( data ) {
		$.ajax( {
			type: 'POST',
			url: window.ajaxurl,
			data,
			success: this.Util.reloadLocation,
			async: false
		} );
	}
}

export default RelationshipControl;
