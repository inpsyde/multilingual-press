const $ = window.jQuery;
const { _ } = window;

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
		this.$content = $( '#content' );

		/**
		 * The jQuery object representing the input element that contains the currently edited post's excerpt.
		 * @type {jQuery}
		 */
		this.$excerpt = $( '#excerpt' );

		/**
		 * The jQuery object representing the input element that contains the currently edited post's title.
		 * @type {jQuery}
		 */
		this.$title = $( '#title' );

		/**
		 * The event manager object.
		 * @type {EventManager}
		 */
		this.EventManager = options.EventManager;

		/**
		 * The model object.
		 * @type {Model}
		 */
		this.model = options.model;
		this.listenTo( this.model, 'change', this.updatePostData );

		/**
		 * The module settings.
		 * @type {Object}
		 */
		this.moduleSettings = options.moduleSettings;

		/**
		 * The currently edited post's ID.
		 * @type {number}
		 */
		this.postID = Number( $( '#post_ID' ).val() );
	}

	/**
	 * Returns the content of the original post.
	 * @returns {string} The post content.
	 */
	get content() {
		return this.$content.val() || '';
	}

	/**
	 * Returns the excerpt of the original post.
	 * @returns {string} The post excerpt.
	 */
	get excerpt() {
		return this.$excerpt.val() || '';
	}

	/**
	 * Returns the slug of the original post.
	 * @returns {string} The post slug.
	 */
	get slug() {
		// Since editing the permalink replaces the "edit slug box" markup, the slug DOM element cannot be cached.
		return $( '#editable-post-name-full' ).text() || '';
	}

	/**
	 * Returns the title of the original post.
	 * @returns {string} The post title.
	 */
	get title() {
		return this.$title.val() || '';
	}

	/**
	 * Copies the post data of the source post to a translation post.
	 * @param {Event} event - The click event of a "Copy source post" button.
	 */
	copyPostData( event ) {
		const remoteSiteID = this.getRemoteSiteID( $( event.target ) );

		let data = {};

		event.preventDefault();

		this.fadeOutMetaBox( remoteSiteID );

		$( '#mlp-translation-data-' + remoteSiteID + '-copied-post' ).val( 1 );

		/**
		 * Triggers the event before copying post data, and passes an object for adding custom data, and the current
		 * site and post IDs and the remote site ID.
		 */
		this.EventManager.trigger(
			'CopyPost:copyPostData',
			data,
			this.moduleSettings.siteID,
			this.postID,
			remoteSiteID
		);

		data = _.extend( data, {
			action: this.moduleSettings.action,
			current_post_id: this.postID,
			remote_site_id: remoteSiteID,
			title: this.title,
			slug: this.slug,
			content: this.content,
			excerpt: this.excerpt
		} );

		this.model.fetch( {
			data,
			processData: true
		} );
	}

	/**
	 * Returns the site ID data attribute value of the given "Copy source post" button.
	 * @param {jQuery} $button - A "Copy source post" button.
	 * @returns {number} The site ID.
	 */
	getRemoteSiteID( $button ) {
		return Number( $button.data( 'site-id' ) );
	}

	/**
	 * Fades the meta box out.
	 * @param {number} remoteSiteID - The remote site ID.
	 */
	fadeOutMetaBox( remoteSiteID ) {
		$( '#inpsyde_multilingual_' + remoteSiteID ).css( 'opacity', .4 );
	}

	/**
	 * Updates the post data in the according meta box for the given site ID.
	 * @returns {boolean} Whether or not the post data have been updated.
	 */
	updatePostData() {
		let data,
			prefix;

		if ( ! this.model.get( 'success' ) ) {
			return false;
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
		this.EventManager.trigger( 'CopyPost:updatePostData', data );

		this.fadeInMetaBox( data.siteID );

		return true;
	}

	/**
	 * Sets the given content for the tinyMCE editor with the given ID.
	 * @param {string} editorID - The tinyMCE editor's ID.
	 * @param {string} content - The content.
	 * @returns {boolean} Whether or not the post content has been updated.
	 */
	setTinyMCEContent( editorID, content ) {
		let editor;

		if ( 'undefined' === typeof window.tinyMCE ) {
			return false;
		}

		editor = window.tinyMCE.get( editorID );
		if ( ! editor ) {
			return false;
		}

		editor.setContent( content );

		return true;
	}

	/**
	 * Fades the meta box in.
	 * @param {number} remoteSiteID - The remote site ID.
	 */
	fadeInMetaBox( remoteSiteID ) {
		$( '#inpsyde_multilingual_' + remoteSiteID ).css( 'opacity', 1 );
	}
}

export default CopyPost;
