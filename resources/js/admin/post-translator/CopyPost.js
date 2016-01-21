/* global MultilingualPress */
(function( $ ) {
	'use strict';

	/**
	 * Constructor for the MultilingualPress CopyPost module.
	 * @constructor
	 */
	var CopyPost = Backbone.View.extend( {
		el: '#post-body',

		events: {
			'click .mlp-copy-post-button': 'copyPostData'
		},

		/**
		 * Initializes the CopyPost module.
		 */
		initialize: function() {
			this.$content = $( '#content' );

			this.$excerpt = $( '#excerpt' );

			this.$slug = $( '#editable-post-name' );

			this.$title = $( '#title' );
		},

		/**
		 * Copies the post data of the source post to a translation post.
		 * @param {Event} event - The click event of a "Copy source post" button.
		 */
		copyPostData: function( event ) {
			var prefix = 'mlp-translation-data-' + this.getSiteID( $( event.target ) );

			event.preventDefault();

			$( '#' + prefix + '-title' ).val( this.getTitle() );

			$( '#' + prefix + '-name' ).val( this.getSlug() );

			this.copyTinyMCEContent( prefix + '-content' );

			$( '#' + prefix + '-content' ).val( this.getContent() );

			$( '#' + prefix + '-excerpt' ).val( this.getExcerpt() );
		},

		/**
		 * Returns the site ID data attribute value of the given "Copy source post" button.
		 * @param {Object} $button - A "Copy source post" button.
		 * @returns {number} -  The site ID.
		 */
		getSiteID: function( $button ) {
			return $button.data( 'site-id' ) || 0;
		},

		/**
		 * Returns the title of the original post.
		 * @returns {string}
		 */
		getTitle: function() {
			return this.$title.val() || '';
		},

		/**
		 * Returns the slug of the original post.
		 * @returns {string}
		 */
		getSlug: function() {
			return this.$slug.text() || '';
		},

		/**
		 * Copies the content of the main TinyMCE editor to the TinyMCE editor with the given ID.
		 * @param {string} targetEditorID - The target TinyMCE editor's ID.
		 */
		copyTinyMCEContent: function( targetEditorID ) {
			var sourceEditor,
				targetEditor;

			if ( 'undefined' === typeof window.tinyMCE ) {
				return;
			}

			sourceEditor = window.tinyMCE.get( 'content' );
			if ( ! sourceEditor ) {
				return;
			}

			targetEditor = window.tinyMCE.get( targetEditorID );
			if ( ! targetEditor ) {
				return;
			}

			targetEditor.setContent( sourceEditor.getContent() );
		},

		/**
		 * Returns the content of the original post.
		 * @returns {string}
		 */
		getContent: function() {
			return this.$content.val() || '';
		},

		/**
		 * Returns the excerpt of the original post.
		 * @returns {string}
		 */
		getExcerpt: function() {
			return this.$excerpt.val() || '';
		}
	} );

	// Register the CopyPost module for the Edit Post and Add New Post admin pages.
	MultilingualPress.registerModule( [ 'post.php', 'post-new.php' ], 'CopyPost', CopyPost );
})( jQuery );
