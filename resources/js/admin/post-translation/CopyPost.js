const { _, jQuery: $ } = window;

// Internal pseudo-namespace for private data.
// NOTE: _this is shared between ALL instances of this module! So far, there is only one instance, so no problem NOW.
const _this = {};

/**
 * The MultilingualPress CopyPost module.
 */
class CopyPost extends Backbone.View {
	/**
	 * Constructor. Sets up the properties.
	 * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
	 */
	constructor( options = {} ) {
		super( options );

		/**
		 * The jQuery object representing the input element that contains the currently edited post's content.
		 * @type {jQuery}
		 */
		_this.$content = $( '#content' );

		/**
		 * The jQuery object representing the input element that contains the currently edited post's excerpt.
		 * @type {jQuery}
		 */
		_this.$excerpt = $( '#excerpt' );

		/**
		 * The jQuery object representing the input element that contains the currently edited post's title.
		 * @type {jQuery}
		 */
		_this.$title = $( '#title' );

		/**
		 * The event manager object.
		 * @type {EventManager}
		 */
		_this.EventManager = options.EventManager;

		/**
		 * The currently edited post's ID.
		 * @type {Number}
		 */
		_this.postId = Number( $( '#post_ID' ).val() || 0 );

		/**
		 * The settings.
		 * @type {Object}
		 */
		_this.settings = options.settings;

		this.listenTo( this.model, 'change:data', this.updatePostData );
	}

	/**
	 * Returns the content of the original post.
	 * @returns {String} The post content.
	 */
	get content() {
		return _this.$content.val() || '';
	}

	/**
	 * Returns the excerpt of the original post.
	 * @returns {String} The post excerpt.
	 */
	get excerpt() {
		return _this.$excerpt.val() || '';
	}

	/**
	 * Returns the currently edited post's ID.
	 * @returns {Number} The currently edited post's ID.
	 */
	get postId() {
		return _this.postId;
	}

	/**
	 * Returns the settings.
	 * @returns {Object} The settings.
	 */
	get settings() {
		return _this.settings;
	}

	/**
	 * Returns the slug of the original post.
	 * @returns {String} The post slug.
	 */
	get slug() {
		// Since editing the permalink replaces the "edit slug box" markup, the slug DOM element cannot be cached.
		return $( '#editable-post-name-full' ).text() || '';
	}

	/**
	 * Returns the TinyMCE content of the original post.
	 * @returns {string} The post content.
	 */
	get tinyMceContent() {
		if ( 'undefined' !== typeof window.tinyMCE ) {
			/**
			 * The TinyMCE instance of the currently edited post's visual editor.
			 * @type {Object}
			 */
			_this.tinyMce = window.tinyMCE.get( 'content' );
		}

		return _this.tinyMce ? _this.tinyMce.getContent() : '';
	}

	/**
	 * Returns the title of the original post.
	 * @returns {String} The post title.
	 */
	get title() {
		return _this.$title.val() || '';
	}

	/**
	 * Copies the post data of the source post to a translation post.
	 * @param {Event} event - The click event of a "Copy source post" button.
	 */
	copyPostData( event ) {
		const remoteSiteId = this.getRemoteSiteId( $( event.target ) );

		let data = {};

		event.preventDefault();

		this.fadeOutMetaBox( remoteSiteId );

		$( `#mlp-translation-data-${remoteSiteId}-copied-post` ).val( 1 );

		/**
		 * Triggers the event before copying post data, and passes an object for adding custom data, and the current
		 * site and post IDs and the remote site ID.
		 */
		_this.EventManager.trigger(
			'CopyPost:copyPostData',
			data,
			this.settings.siteId,
			this.postId,
			remoteSiteId
		);

		data = _.extend( data, {
			action: this.settings.action,
			current_post_id: this.postId,
			remote_site_id: remoteSiteId,
			title: this.title,
			slug: this.slug,
			content: this.content,
			tinyMceContent: this.tinyMceContent,
			excerpt: this.excerpt
		} );

		this.model.save( data, {
			data,
			processData: true
		} );
	}

	/**
	 * Returns the site ID data attribute value of the given "Copy source post" button.
	 * @param {jQuery} $button - A "Copy source post" button.
	 * @returns {Number} The site ID.
	 */
	getRemoteSiteId( $button ) {
		return Number( $button.data( 'site-id' ) || 0 );
	}

	/**
	 * Fades the meta box out.
	 * @param {Number} remoteSiteId - The remote site ID.
	 */
	fadeOutMetaBox( remoteSiteId ) {
		$( `#inpsyde_multilingual_${remoteSiteId}` ).css( 'opacity', .4 );
	}

	/**
	 * Updates the post data in the according meta box for the given site ID.
	 * @returns {Boolean} Whether or not the post data have been updated.
	 */
	updatePostData() {
		if ( ! this.model.get( 'success' ) ) {
			return false;
		}

		const data = this.model.get( 'data' );

		const prefix = `mlp-translation-data-${data.siteId}-`;

		$( `#${prefix}title` ).val( data.title );

		$( `#${prefix}name` ).val( data.slug );

		this.setTinyMceContent( `${prefix}content`, data.tinyMceContent );

		$( `#${prefix}content` ).val( data.content );

		$( `#${prefix}excerpt` ).val( data.excerpt );

		/**
		 * Triggers the event for updating the post, and passes the according data.
		 */
		_this.EventManager.trigger( 'CopyPost:updatePostData', data );

		this.fadeInMetaBox( data.siteId );

		return true;
	}

	/**
	 * Sets the given content for the tinyMCE editor with the given ID.
	 * @param {String} editorId - The tinyMCE editor's ID.
	 * @param {String} content - The content.
	 * @returns {Boolean} Whether or not the post content has been updated.
	 */
	setTinyMceContent( editorId, content ) {
		if ( 'undefined' === typeof window.tinyMCE ) {
			return false;
		}

		const editor = window.tinyMCE.get( editorId );
		if ( ! editor ) {
			return false;
		}

		editor.setContent( content );

		return true;
	}

	/**
	 * Fades the meta box in.
	 * @param {Number} remoteSiteId - The remote site ID.
	 */
	fadeInMetaBox( remoteSiteId ) {
		$( `#inpsyde_multilingual_${remoteSiteId}` ).css( 'opacity', 1 );
	}
}

export default CopyPost;
