/* global ajaxurl, MultilingualPress */
(function( $ ) {
	'use strict';

	/**
	 * Settings for the MultilingualPress NavMenus module. Only available on the targeted admin pages.
	 * @type {Object}
	 */
	var moduleSettings = MultilingualPress.getSettings( 'NavMenus' );

	/**
	 * Constructor for the MultilingualPress NavMenuItem model.
	 * @class
	 */
	var NavMenuItem = Backbone.Model.extend( {
		/** @lends NavMenuItem.prototype */

		/**
		 * Model containing Nav Menu data
		 * @augments Backbone.Model
		 * @constructs
		 * @name NavMenuItem
		 */

		/**
		 * @type {string}
		 */
		urlRoot: ajaxurl
	} );

	/**
	 * Constructor for the MultilingualPress NavMenus module.
	 * @class
	 */
	var NavMenus = Backbone.View.extend( {
		/** @lends NavMenus.prototype */

		el: '#' + moduleSettings.metaBoxID,

		events: {
			'click #submit-mlp-language': 'sendRequest'
		},

		/**
		 * Initializes the NavMenus module.
		 *
		 * @augments Backbone.View
		 * @constructs
		 * @name NavMenus
		 */
		initialize: function() {

			this.$languages = this.$el.find( 'li [type="checkbox"]' );

			this.$menu = $( '#menu' );

			this.$menuToEdit = $( '#menu-to-edit' );

			this.$spinner = this.$el.find( '.spinner' );

			this.$submit = this.$el.find( '#submit-mlp-language' );

			this.model = new NavMenuItem();
			this.listenTo( this.model, 'change', this.render );
		},

		/**
		 * Requests the according markup for the checked languages in the Languages meta box.
		 * @param {Event} event - The click event of the submit button.
		 */
		sendRequest: function( event ) {
			var data = {
				action: moduleSettings.action,
				menu: this.$menu.val(),
				mlp_sites: this.getSites()
			};
			data[ moduleSettings.nonceName ] = moduleSettings.nonce;

			event.preventDefault();

			this.$submit.prop( 'disabled', true );

			/**
			 * The "is-active" class was introduced in WordPress 4.2. Since MultilingualPress has to stay
			 * backwards-compatible with the last four major versions of WordPress, we can only rely on this with the
			 * release of WordPress 4.6.
			 * TODO: Remove "show()" with the release of WordPress 4.6.
			 */
			this.$spinner.addClass( 'is-active' ).show();

			this.model.fetch( {
				data: data,
				processData: true
			} );
		},

		/**
		 * Returns the site IDs for the checked languages in the Languages meta box.
		 * @returns {number[]} - The site IDs.
		 */
		getSites: function() {
			var languages = [];

			this.$languages.filter( ':checked' ).each( function() {
				languages.push( Number( $( this ).val() || 0 ) );
			} );

			return languages;
		},

		/**
		 * Renders the nav menu item to the currently edited menu.
		 */
		render: function() {
			if ( this.model.get( 'success' ) ) {
				this.$menuToEdit.append( this.model.get( 'data' ) );
			}

			this.$languages.prop( 'checked', false );

			/**
			 * The "is-active" class was introduced in WordPress 4.2. Since MultilingualPress has to stay
			 * backwards-compatible with the last four major versions of WordPress, we can only rely on this with the
			 * release of WordPress 4.6.
			 * TODO: Remove "hide()" with the release of WordPress 4.6.
			 */
			this.$spinner.addClass( 'is-active' ).hide();

			this.$submit.prop( 'disabled', false );
		}
	} );

	// Register the NavMenus module for the Menus admin page.
	MultilingualPress.registerModule( 'nav-menus.php', 'NavMenus', NavMenus );
})( jQuery );
