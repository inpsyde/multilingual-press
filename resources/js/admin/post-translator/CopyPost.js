/* global ajaxurl, MultilingualPress */
(function( $ ) {
	'use strict';

	/**
	 * Settings for the MultilingualPress CopyPost module. Only available on the targeted admin pages.
	 * @type {Object}
	 */
	var moduleSettings = MultilingualPress.getSettings( 'CopyPost' );

	/**
	 * Constructor for the MultilingualPress PostData model.
	 * @constructor
	 */
	var PostData = Backbone.Model.extend( {
		urlRoot: ajaxurl
	} );

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

			this.model = new PostData();
			this.listenTo( this.model, 'change', this.updatePostData );

			this.postID = $( '#post_ID' ).val();
		},

		/**
		 * Copies the post data of the source post to a translation post.
		 * @param {Event} event - The click event of a "Copy source post" button.
		 */
		copyPostData: function( event ) {
			var data = {},
				remoteSiteID = this.getRemoteSiteID( $( event.target ) );

			event.preventDefault();

			$( '#mlp-translation-data-' + remoteSiteID + '-copied-post' ).val( 1 );

			/**
			 * Triggers the event before copying post data, and passes an object for adding custom data, and the current
			 * site and post IDs and the remote site ID.
			 */
			MultilingualPress.Events.trigger(
				'CopyPost:copyPostData',
				data,
				moduleSettings.siteID,
				this.postID,
				remoteSiteID
			);

			data = _.extend( data, {
				action: moduleSettings.action,
				current_post_id: this.postID,
				remote_site_id: remoteSiteID,
				title: this.getTitle(),
				slug: this.getSlug(),
				content: this.getContent(),
				excerpt: this.getExcerpt()
			} );

			this.model.fetch( {
				data: data,
				processData: true
			} );
		},

		/**
		 * Returns the site ID data attribute value of the given "Copy source post" button.
		 * @param {Object} $button - A "Copy source post" button.
		 * @returns {number} -  The site ID.
		 */
		getRemoteSiteID: function( $button ) {
			return $button.data( 'site-id' ) || 0;
		},

		/**
		 * Returns the title of the original post.
		 * @returns {string} - The post title.
		 */
		getTitle: function() {
			return this.$title.val() || '';
		},

		/**
		 * Returns the slug of the original post.
		 * @returns {string} - The post slug.
		 */
		getSlug: function() {
			return this.$slug.text() || '';
		},

		/**
		 * Returns the content of the original post.
		 * @returns {string} - The post content.
		 */
		getContent: function() {
			return this.$content.val() || '';
		},

		/**
		 * Returns the excerpt of the original post.
		 * @returns {string} - The post excerpt.
		 */
		getExcerpt: function() {
			return this.$excerpt.val() || '';
		},

		/**
		 * Updates the post data in the according meta box for the given site ID.
		 */
		updatePostData: function() {
			var data,
				prefix;

			if ( ! this.model.get( 'success' ) ) {
				return;
			}

			data = this.model.get( 'data' );

			prefix = 'mlp-translation-data-' + data.siteID + '-';

			$( '#' + prefix + 'title' ).val( data.title );

			$( '#' + prefix + 'name' ).val( data.slug );

			this.setTinyMCEContent( prefix + 'content', data.content );

			$( '#' + prefix + 'content' ).val( data.content );

			$( '#' + prefix + 'excerpt' ).val( data.excerpt );

			/**
			 * Triggers the event for updating the post, and passes the according data.
			 */
			MultilingualPress.Events.trigger( 'CopyPost:updatePostData', data );
		},

		/**
		 * Sets the given content for the tinyMCE editor with the given ID.
		 * @param {string} editorID - The tinyMCE editor's ID.
		 * @param {string} content - The content.
		 */
		setTinyMCEContent: function( editorID, content ) {
			var editor;

			if ( 'undefined' === typeof window.tinyMCE ) {
				return;
			}

			editor = window.tinyMCE.get( editorID );
			if ( ! editor ) {
				return;
			}

			editor.setContent( content );
		}
	} );

	// Register the CopyPost module for the Edit Post and Add New Post admin pages.
	MultilingualPress.registerModule( [ 'post.php', 'post-new.php' ], 'CopyPost', CopyPost );
})( jQuery );
