const $ = window.jQuery;

/**
 * The MultilingualPress NavMenus module.
 */
class NavMenus extends Backbone.View {
	/**
	 * Constructor. Sets up the properties.
	 * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
	 */
	constructor( options = {} ) {
		super( options );

		/**
		 * The jQuery object representing the MultilingualPress language checkboxes.
		 * @type {jQuery}
		 */
		this.$languages = this.$el.find( 'li [type="checkbox"]' );

		/**
		 * The jQuery object representing the input element that contains the currently edited menu's ID.
		 * @type {jQuery}
		 */
		this.$menu = $( '#menu' );

		/**
		 * The jQuery object representing the currently edited menu.
		 * @type {jQuery}
		 */
		this.$menuToEdit = $( '#menu-to-edit' );

		/**
		 * The jQuery object representing the Languages meta box spinner.
		 * @type {jQuery}
		 */
		this.$spinner = this.$el.find( '.spinner' );

		/**
		 * The jQuery object representing the Languages meta box submit button.
		 * @type {jQuery}
		 */
		this.$submit = this.$el.find( '#submit-mlp-language' );

		/**
		 * The model object.
		 * @type {Model}
		 */
		this.model = options.model;
		this.listenTo( this.model, 'change', this.render );

		/**
		 * The module settings.
		 * @type {Object}
		 */
		this.moduleSettings = options.moduleSettings;
	}

	/**
	 * Returns the site IDs for the checked languages in the Languages meta box.
	 * @returns {number[]} The site IDs.
	 */
	get sites() {
		const ids = [];

		this.$languages.filter( ':checked' ).each( ( index, element ) => ids.push( Number( $( element ).val() ) ) );

		return ids;
	}

	/**
	 * Requests the according markup for the checked languages in the Languages meta box.
	 * @param {Event} event - The click event of the submit button.
	 */
	sendRequest( event ) {
		const data = {
			action: this.moduleSettings.action,
			menu: this.$menu.val(),
			mlp_sites: this.sites
		};
		data[ this.moduleSettings.nonceName ] = this.moduleSettings.nonce;

		event.preventDefault();

		this.$submit.prop( 'disabled', true );

		this.$spinner.addClass( 'is-active' );

		this.model.fetch( {
			data,
			processData: true
		} );
	}

	/**
	 * Renders the nav menu item to the currently edited menu.
	 */
	render() {
		if ( this.model.get( 'success' ) ) {
			this.$menuToEdit.append( this.model.get( 'data' ) );
		}

		this.$languages.prop( 'checked', false );

		this.$spinner.removeClass( 'is-active' );

		this.$submit.prop( 'disabled', false );
	}
}

export default NavMenus;
