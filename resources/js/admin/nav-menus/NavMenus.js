/* global ajaxurl */
(function( $, MultilingualPress ) {
	'use strict';

	/**
	 * Settings for the MultilingualPress NavMenus module. Only available on the targeted admin pages.
	 * @type {Object}
	 */
	var moduleSettings = MultilingualPress.getSettings( 'NavMenus' );

	/**
	 * @class NavMenuItem
	 * @classdesc MultilingualPress nav menu item model.
	 * @extends Backbone.Model
	 */
	var NavMenuItem = Backbone.Model.extend( /** @lends NavMenuItem# */ {
		urlRoot: ajaxurl
	} );

	var NavMenus = Backbone.View.extend( /** @lends NavMenus# */ {
		/**
		 * @constructs NavMenus
		 * @classdesc MultilingualPress NavMenus module.
		 * @extends Backbone.View
		 */
		initialize: function() {
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
		 * @returns {int[]} The site IDs.
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
	MultilingualPress.registerModule( 'nav-menus.php', 'NavMenus', NavMenus, {
		el: '#' + moduleSettings.metaBoxID,
		events: {
			'click #submit-mlp-language': 'sendRequest'
		}
	} );
})( jQuery, window.MultilingualPress );
