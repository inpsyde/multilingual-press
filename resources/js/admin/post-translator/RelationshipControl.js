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
		el: '',

		events: {
		},

		/**
		 * Initializes the RelationshipControl module.
		 */
		initialize: function() {
		}
	} );

	// Register the RelationshipControl module for the Add New Post and the Edit Post admin pages.
	//MultilingualPress.registerModule( [ 'post.php', 'post-new.php' ], 'RelationshipControl', RelationshipControl );
})( jQuery );
