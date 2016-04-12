const $ = window.jQuery;
const { _ } = window;

/**
 * Animations for the MultilingualPress CopyPost Module.
 */
class CopyPostAnimation extends Backbone.View {
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

		this.EventManager.on( 'CopyPost:copyPostData', this.fadeOut, this );
		this.EventManager.on( 'CopyPost:updatePostData', this.fadeIn, this );
	}

	/**
	 * Fades the Metabox out
	 * @param {Object} data - Post data.
	 * @param {int} siteID - The current site ID
	 * @param {int} postID - The current post ID
	 * @param {int} remoteSiteID - The remote post ID
	 */
	fadeOut( data, siteID, postID, remoteSiteID ) {
		$( '#inpsyde_multilingual_' + remoteSiteID ).css( 'opacity', 0.4 );
	}

	/**
	 * Fades the Metabox in
	 * @param data
	 */
	fadeIn( data ) {
		$( '#inpsyde_multilingual_' + data.siteID ).css( 'opacity', 1 );
	}
}

export default CopyPostAnimation;
