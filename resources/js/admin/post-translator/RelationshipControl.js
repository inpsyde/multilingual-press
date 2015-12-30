/* global MultilingualPress */
(function( $ ) {
	'use strict';

	/**
	 * Settings for the MultilingualPress RelationshipControl module. Only available on the targeted admin pages.
	 * @type {Object}
	 */
	var moduleSettings = MultilingualPress.getSettings( 'RelationshipControl' );

	/**
	 * Constructor for the MultilingualPress RelationshipControl module.
	 * @constructor
	 */
	var RelationshipControl = Backbone.View.extend( {
		el: 'body',

		events: {
			'change .mlp_rsc_action_list input': 'updateUnsavedRelationships',
			'click #publish': 'confirmUnsavedRelationships'
		},

		/**
		 * Initializes the RelationshipControl module.
		 */
		initialize: function() {
			this.unsavedRelationships = [];
		},

		/**
		 * Updates the unsaved relationships array for the meta box containing the changed radio input element.
		 * @param {Event} event - The change event of a radio input element.
		 */
		updateUnsavedRelationships: function( event ) {
			var $input = $( event.target ),
				$metaBox = $input.closest( '.mlp_advanced_translator_metabox' ),
				index = this.findMetaBox( $metaBox ),
				stay = 'stay' === $input.val();

			if ( -1 === index ) {
				if ( ! stay ) {
					this.unsavedRelationships.push( $metaBox );
				}
			} else if ( stay ) {
				this.unsavedRelationships.splice( index, 1 );
			}
		},

		/**
		 * Returns the index of the given meta box in the unsaved relationships array, if included, and -1 on failure.
		 * @param {Object} $metaBox - The meta box element.
		 * @returns {number} - The index of the meta box.
		 */
		findMetaBox: function( $metaBox ) {
			$.each( this.unsavedRelationships, function( index, element ) {
				if ( element === $metaBox) {
					return index;
				}
			} );

			return -1;
		},

		/**
		 * Displays a confirm dialog informing the user about unsaved relationships.
		 * @param {Event} event - The click event of the publish button.
		 */
		confirmUnsavedRelationships: function( event ) {
			if ( this.unsavedRelationships.length && ! confirm( moduleSettings.L10n.unsavedRelationships ) ) {
				event.preventDefault();
				event.stopPropagation();
			}
		}
	} );

	// Register the RelationshipControl module for the Add New Post and the Edit Post admin pages.
	MultilingualPress.registerModule( [ 'post.php', 'post-new.php' ], 'RelationshipControl', RelationshipControl );
})( jQuery );
