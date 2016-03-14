/* global mlpSettings */
(function( $ ) {
	'use strict';

	/**
	 * @class MultilingualPressRouter
	 * @classdesc MultilingualPress router.
	 * @extends Backbone.Router
	 */
	var MultilingualPressRouter = Backbone.Router.extend( {} );

	/**
	 * @class MultilingualPressAdmin
	 * @classdesc MultilingualPress admin controller.
	 */
	var MultilingualPressAdmin = function() {
		var Modules = [],
			Registry = {},
			Router = new MultilingualPressRouter();

		/**
		 * Registers the module with the given data for the given route.
		 * @param {Object} moduleData - The module data.
		 * @param {string} route - The route.
		 */
		var registerModuleForRoute = function( moduleData, route ) {
			if ( Registry[ route ] ) {
				Registry[ route ].modules.push( moduleData );
			} else {
				Registry[ route ] = {
					modules: [ moduleData ]
				};
			}
		};

		/**
		 * Sets up all routes with the according registered modules.
		 */
		var setUpRoutes = function() {
			$.each( Registry, function( route, routeData ) {
				Router.route( route, route, function() {
					$.each( routeData.modules, function( index, module ) {
						Modules[ module.name ] = new module.Callback( module.options );
					} );
				} );
			} );
		};

		return /** @lends MultilingualPressAdmin# */ {
			/**
			 * Events module.
			 * @type {Object}
			 * @extends Backbone.Events
			 */
			Events: _.extend( {}, Backbone.Events ),

			/**
			 * MultilingualPress module instances.
			 * @type {Object[]}
			 */
			Modules: Modules,

			/**
			 * Returns the settings object for the given module or settings name.
			 * @param {string} name - The name of either the MulitilingualPress module or the settings object itself.
			 * @returns {Object} The settings object.
			 */
			getSettings: function( name ) {
				if ( 'undefined' !== typeof window[ 'mlp' + name + 'Settings' ] ) {
					return window[ 'mlp' + name + 'Settings' ];
				}

				if ( 'undefined' !== typeof window[ name ] ) {
					return window[ name ];
				}

				return {};
			},

			/**
			 * Registers a new module with the given Module callback under the given name for the given route.
			 * @param {string|string[]} routes - The routes for the module.
			 * @param {string} name - The name of the module.
			 * @param {Function} Module - The constructor callback for the module.
			 * @param {Object} [options={}] - Optional. The options for the module. Default to {}.
			 */
			registerModule: function( routes, name, Module, options ) {
				var moduleData = {
					name: name,
					Callback: Module,
					options: options || {}
				};

				$.each( _.isArray( routes ) ? routes : [ routes ], function( index, route ) {
					registerModuleForRoute( moduleData, route );
				} );
			},

			/**
			 * Initializes the instance.
			 */
			initialize: function() {
				setUpRoutes();

				Backbone.history.start( {
					root: mlpSettings.urlRoot,
					pushState: true,
					hashChange: false
				} );
			}
		};
	};

	/**
	 * The MultilingualPress admin instance.
	 * @type {MultilingualPressAdmin}
	 */
	window.MultilingualPress = new MultilingualPressAdmin();

	$( window.MultilingualPress.initialize );
})( jQuery );

(function( $, MultilingualPress ) {
	'use strict';

	var Common = Backbone.View.extend( /** @lends Common# */ {
		/**
		 * @constructs Common
		 * @classdesc MultilingualPress Common module.
		 * @extends Backbone.View
		 */
		initialize: function() {
			this.initializeStateTogglers();
		},

		/**
		 * Initializes the togglers that work by using their individual state.
		 */
		initializeStateTogglers: function() {
			$( '.mlp-state-toggler' ).each( function( index, element ) {
				var $toggler = $( element );
				$( '[name="' + $toggler.attr( 'name' ) + '"]' ).on( 'change', {
					$toggler: $toggler
				}, this.toggleElementIfChecked );
			}.bind( this ) );
		},

		/**
		 * Toggles the element with the ID given in the according toggler's data attribute if the toggler is checked.
		 * @param {Event} event - The change event of an input element.
		 */
		toggleElementIfChecked: function( event ) {
			var $toggler = event.data.$toggler,
				targetID = $toggler.data( 'toggle-target' );
			if ( targetID ) {
				$( targetID ).toggle( $toggler.is( ':checked' ) );
			}
		},

		/**
		 * Toggles the element with the ID given in the according data attribute.
		 * @param {Event} event - The click event of a toggler element.
		 */
		toggleElement: function( event ) {
			var targetID = $( event.target ).data( 'toggle-target' ) || '';
			if ( targetID ) {
				$( targetID ).toggle();
			}
		}
	} );

	// Register the Common module for all admin pages.
	MultilingualPress.Modules.Common = new Common( {
		el: 'body',
		events: {
			'click .mlp-click-toggler': 'toggleElement'
		}
	} );
})( jQuery, window.MultilingualPress );

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

			this.$spinner.addClass( 'is-active' );

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

			this.$spinner.addClass( 'is-active' );

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

(function( $, MultilingualPress ) {
	'use strict';

	var AddNewSite = Backbone.View.extend( /** @lends AddNewSite# */ {
		/**
		 * @constructs AddNewSite
		 * @classdesc MultilingualPress AddNewSite module.
		 * @extends Backbone.View
		 */
		initialize: function() {
			this.template = _.template( $( '#mlp-add-new-site-template' ).html() || '' );

			// First render the template, ...
			this.render();

			// ...then set up the properties using elements that just have been injected into the DOM.
			/**
			 * The jQuery object representing the MultilingualPress language select.
			 * @type {jQuery}
			 */
			this.$language = $( '#mlp-site-language' );

			/**
			 * The jQuery object representing the table row that contains the plugin activation checkbox.
			 * @type {jQuery}
			 */
			this.$pluginsRow = $( '#mlp-activate-plugins' ).closest( 'tr' );
		},

		/**
		 * Renders the MultilingualPress table markup.
		 */
		render: function() {
			this.$el.find( '.submit' ).before( this.template() );
		},

		/**
		 * Sets MultilingualPress's language select to the currently selected site language.
		 * @param {Event} event - The change event of the site language select element.
		 */
		adaptLanguage: function( event ) {
			var language = this.getLanguage( $( event.target ) );
			if ( this.$language.find( '[value="' + language + '"]' ).length ) {
				this.$language.val( language );
			}
		},

		/**
		 * Returns the selected language of the given select element.
		 * @param {HTMLElement} $select - A select element.
		 * @returns {string} The selected language.
		 */
		getLanguage: function( $select ) {
			var language = $select.val();
			if ( language ) {
				return language.replace( '_', '-' );
			}

			return 'en-US';
		},

		/**
		 * Toggles the Plugins row according to the source site ID select element's value.
		 * @param {Event} event - The change event of the source site ID select element.
		 */
		togglePluginsRow: function( event ) {
			this.$pluginsRow.toggle( 0 < $( event.target ).val() );
		}
	} );

	// Register the AddNewSite module for the Add New Site network admin page.
	MultilingualPress.registerModule( 'network/site-new.php', 'AddNewSite', AddNewSite, {
		el: '#wpbody-content form',
		events: {
			'change #site-language': 'adaptLanguage',
			'change #mlp-base-site-id': 'togglePluginsRow'
		}
	} );
})( jQuery, window.MultilingualPress );

/* global ajaxurl */
(function( $, MultilingualPress ) {
	'use strict';

	/**
	 * Settings for the MultilingualPress CopyPost module. Only available on the targeted admin pages.
	 * @type {Object}
	 */
	var moduleSettings = MultilingualPress.getSettings( 'CopyPost' );

	/**
	 * @class PostData
	 * @classdesc MultilingualPress PostData model.
	 * @extends Backbone.Model
	 */
	var PostData = Backbone.Model.extend( /** @lends PostData# */ {
		urlRoot: ajaxurl
	} );

	var CopyPost = Backbone.View.extend( /** @lends CopyPost# */ {
		/**
		 * @constructs CopyPost
		 * @classdesc MultilingualPress CopyPost module.
		 * @extends Backbone.View
		 */
		initialize: function() {
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
			 * The jQuery object representing the input element that contains the currently edited post's slug.
			 * @type {jQuery}
			 */
			this.$slug = $( '#editable-post-name' );

			/**
			 * The jQuery object representing the input element that contains the currently edited post's title.
			 * @type {jQuery}
			 */
			this.$title = $( '#title' );

			this.model = new PostData();
			this.listenTo( this.model, 'change', this.updatePostData );

			/**
			 * The currently edited post's ID.
			 * @type {number}
			 */
			this.postID = Number( $( '#post_ID' ).val() );
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
		 * @param {jQuery} $button - A "Copy source post" button.
		 * @returns {number} The site ID.
		 */
		getRemoteSiteID: function( $button ) {
			return $button.data( 'site-id' ) || 0;
		},

		/**
		 * Returns the title of the original post.
		 * @returns {string} The post title.
		 */
		getTitle: function() {
			return this.$title.val() || '';
		},

		/**
		 * Returns the slug of the original post.
		 * @returns {string} The post slug.
		 */
		getSlug: function() {
			return this.$slug.text() || '';
		},

		/**
		 * Returns the content of the original post.
		 * @returns {string} The post content.
		 */
		getContent: function() {
			return this.$content.val() || '';
		},

		/**
		 * Returns the excerpt of the original post.
		 * @returns {string} The post excerpt.
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
	MultilingualPress.registerModule( [ 'post.php', 'post-new.php' ], 'CopyPost', CopyPost, {
		el: '#post-body',
		events: {
			'click .mlp-copy-post-button': 'copyPostData'
		}
	} );
})( jQuery, window.MultilingualPress );

/* global ajaxurl */
(function( $, MultilingualPress ) {
	'use strict';

	/**
	 * Settings for the MultilingualPress RelationshipControl module. Only available on the targeted admin pages.
	 * @type {Object}
	 */
	var moduleSettings = MultilingualPress.getSettings( 'RelationshipControl' );

	var RelationshipControl = Backbone.View.extend( /** @lends RelationshipControl# */ {
		/**
		 * @constructs RelationshipControl
		 * @classdesc MultilingualPress RelationshipControl module.
		 * @extends Backbone.View
		 */
		initialize: function() {
			/**
			 * Array of jQuery objects representing meta boxes with unsaved relationships.
			 * @type {jQuery[]}
			 */
			this.unsavedRelationships = [];

			this.initializeEventHandlers();
		},

		/**
		 * Initializes the event handlers for all custom relationship control events.
		 */
		initializeEventHandlers: function() {
			MultilingualPress.Events.on( {
				'RelationshipControl:connectExistingPost': this.connectExistingPost,
				'RelationshipControl:connectNewPost': this.connectNewPost,
				'RelationshipControl:disconnectPost': this.disconnectPost
			}, this );
		},

		/**
		 * Updates the unsaved relationships array for the meta box containing the changed radio input element.
		 * @param {Event} event - The change event of a radio input element.
		 */
		updateUnsavedRelationships: function( event ) {
			var $input = $( event.target ),
				$metaBox = $input.closest( '.mlp-translation-meta-box' ),
				$button = $metaBox.find( '.mlp-save-relationship-button' ),
				index = this.findMetaBox( $metaBox );

			if ( 'stay' === $input.val() ) {
				$button.prop( 'disabled', 'disabled' );

				if ( -1 !== index ) {
					this.unsavedRelationships.splice( index, 1 );
				}
			} else if ( -1 === index ) {
				this.unsavedRelationships.push( $metaBox );

				$button.removeAttr( 'disabled' );
			}
		},

		/**
		 * Returns the index of the given meta box in the unsaved relationships array, and -1 if not found.
		 * @param {jQuery} $metaBox - The meta box element.
		 * @returns {number} The index of the meta box.
		 */
		findMetaBox: function( $metaBox ) {
			$.each( this.unsavedRelationships, function( index, element ) {
				if ( element === $metaBox ) {
					return index;
				}
			} );

			return -1;
		},

		/**
		 * Displays a confirm dialog informing the user about unsaved relationships, if any.
		 * @param {Event} event - The click event of the publish button.
		 */
		confirmUnsavedRelationships: function( event ) {
			if ( this.unsavedRelationships.length && ! window.confirm( moduleSettings.L10n.unsavedRelationships ) ) {
				event.preventDefault();
			}
		},

		/**
		 * Triggers the according event in case of changed relationships.
		 * @param {Event} event - The click event of a save relationship button.
		 */
		saveRelationship: function( event ) {
			var $button = $( event.target ),
				remoteSiteID = $button.data( 'remote-site-id' ),
				action = $( 'input[name="mlp-rc-action[' + remoteSiteID + ']"]:checked' ).val(),
				eventName = this.getEventName( action );

			if ( 'stay' === action ) {
				return;
			}

			$button.prop( 'disabled', 'disabled' );

			/**
			 * Triggers the according event for the current relationship action, and passes data and the event's name.
			 */
			MultilingualPress.Events.trigger( 'RelationshipControl:' + eventName, {
				action: 'mlp_rc_' + action,
				remote_site_id: remoteSiteID,
				remote_post_id: $button.data( 'remote-post-id' ),
				source_site_id: $button.data( 'source-site-id' ),
				source_post_id: $button.data( 'source-post-id' )
			}, eventName );
		},

		/**
		 * Returns the according event name for the given relationship action.
		 * @param {string} action - A relationship action.
		 * @returns {string} The event name.
		 */
		getEventName: function( action ) {
			switch ( action ) {
				case 'search':
					return 'connectExistingPost';

				case 'new':
					return 'connectNewPost';

				case 'disconnect':
					return 'disconnectPost';

				default:
					return '';
			}
		},

		/**
		 * Handles changing a post's relationship by connecting a new post.
		 * @param {Object} data - The common data for all relationship requests.
		 */
		connectNewPost: function( data ) {
			data.new_post_title = $( 'input[name="post_title"]' ).val();

			this.sendRequest( data );
		},

		/**
		 * Handles changing a post's relationship by disconnecting the currently connected post.
		 * @param {Object} data - The common data for all relationship requests.
		 */
		disconnectPost: function( data ) {
			this.sendRequest( data );
		},

		/**
		 * Handles changing a post's relationship by connecting an existing post.
		 * @param {Object} data - The common data for all relationship requests.
		 */
		connectExistingPost: function( data ) {
			var newPostID = $( 'input[name="mlp_add_post[' + data.remote_site_id + ']"]:checked' ).val() || 0;
			if ( newPostID ) {
				data.new_post_id = Number( newPostID );

				this.sendRequest( data );
			} else {
				window.alert( moduleSettings.L10n.noPostSelected );
			}
		},

		/**
		 * Changes a post's relationhip by sending a synchronous AJAX request with the according new relationship data.
		 * @param {Object} data - The relationship data.
		 */
		sendRequest: function( data ) {
			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: data,
				success: this.reloadLocation,
				async: false
			} );
		},

		/**
		 * Reloads the current page.
		 */
		reloadLocation: function() {
			window.location.reload( true );
		}
	} );

	// Register the RelationshipControl module for the Add New Post and the Edit Post admin pages.
	MultilingualPress.registerModule( [ 'post.php', 'post-new.php' ], 'RelationshipControl', RelationshipControl, {
		el: 'body',
		events: {
			'change .mlp-rc-actions input': 'updateUnsavedRelationships',
			'click #publish': 'confirmUnsavedRelationships',
			'click .mlp-save-relationship-button': 'saveRelationship'
		}
	} );
})( jQuery, window.MultilingualPress );

/* global ajaxurl */
(function( $, MultilingualPress ) {
	'use strict';

	/**
	 * Settings for the MultilingualPress RemotePostSearch module. Only available on the targeted admin pages.
	 * @type {Object}
	 */
	var moduleSettings = MultilingualPress.getSettings( 'RemotePostSearch' );

	/**
	 * @class RemotePostSearchResult
	 * @classdesc MultilingualPress RemotePostSearchResult model.
	 * @extends Backbone.Model
	 */
	var RemotePostSearchResult = Backbone.Model.extend( /** @lends RemotePostSearchResult# */ {
		urlRoot: ajaxurl
	} );

	var RemotePostSearch = Backbone.View.extend( /** @lends RemotePostSearch# */ {
		/**
		 * @constructs RemotePostSearch
		 * @classdesc MultilingualPress RemotePostSearch module.
		 * @augments Backbone.View
		 */
		initialize: function() {
			/**
			 * Array holding the default search result HTML strings.
			 * @type {string[]}
			 */
			this.defaultResults = [];

			/**
			 * Array holding jQuery objects representing the search result containers.
			 * @type {jQuery[]}
			 */
			this.resultsContainers = [];

			/**
			 * Minimum number of characters required to fire the remote post search.
			 * @type {number}
			 */
			this.searchThreshold = parseInt( moduleSettings.searchThreshold, 10 );

			this.model = new RemotePostSearchResult();
			this.listenTo( this.model, 'change', this.render );

			this.initializeResults();
		},

		/**
		 * Initializes both the default search result views as well as the result containers.
		 */
		initializeResults: function() {
			$( '.mlp-search-field' ).each( function( index, element ) {
				var $element = $( element ),
					$resultsContainer = $( '#' + $element.data( 'results-container-id' ) ),
					siteID = $element.data( 'remote-site-id' );

				this.defaultResults[ siteID ] = $resultsContainer.html();
				this.resultsContainers[ siteID ] = $resultsContainer;
			}.bind( this ) );
		},

		/**
		 * Prevents form submission due to the enter key being pressed.
		 * @param {Event} event - The keydown event of a post search element.
		 */
		preventFormSubmission: function( event ) {
			if ( 13 === event.which ) {
				event.preventDefault();
			}
		},

		/**
		 * According to the user input, either search for posts, or display the initial post selection.
		 * @param {Event} event - The keyup event of a post search element.
		 */
		reactToInput: function( event ) {
			var $input = $( event.target ),
				remoteSiteID,
				value = $.trim( $input.val() || '' );

			if ( value === $input.data( 'value' ) ) {
				return;
			}

			clearTimeout( this.reactToInputTimer );

			$input.data( 'value', value );

			remoteSiteID = $input.data( 'remote-site-id' );

			if ( '' === value ) {
				this.resultsContainers[ remoteSiteID ].html( this.defaultResults[ remoteSiteID ] );
			} else if ( value.length >= this.searchThreshold ) {
				this.reactToInputTimer = setTimeout( function() {
					this.model.fetch( {
						data: {
							action: 'mlp_rc_remote_post_search',
							remote_site_id: remoteSiteID,
							remote_post_id: $input.data( 'remote-post-id' ),
							source_site_id: $input.data( 'source-site-id' ),
							source_post_id: $input.data( 'source-post-id' ),
							s: value
						},
						processData: true
					} );
				}.bind( this ), 400 );
			}
		},

		/**
		 * Renders the found posts to the according results container.
		 */
		render: function() {
			var data;
			if ( this.model.get( 'success' ) ) {
				data = this.model.get( 'data' );

				this.resultsContainers[ data.remoteSiteID ].html( data.html );
			}
		}
	} );

	// Register the RemotePostSearch module for the Add New Post and the Edit Post admin pages.
	MultilingualPress.registerModule( [ 'post.php', 'post-new.php' ], 'RemotePostSearch', RemotePostSearch, {
		el: 'body',
		events: {
			'keydown .mlp-search-field': 'preventFormSubmission',
			'keyup .mlp-search-field': 'reactToInput'
		}
	} );
})( jQuery, window.MultilingualPress );

(function( $, MultilingualPress ) {
	'use strict';

	var TermTranslator = Backbone.View.extend( /** @lends TermTranslator# */ {
		/**
		 * @constructs TermTranslator
		 * @classdesc MultilingualPress TermTranslator module.
		 * @extends Backbone.View
		 */
		initialize: function() {
			/**
			 * The jQuery object representing the MultilingualPress term selects.
			 * @type {jQuery}
			 */
			this.$selects = this.$el.find( 'select' );

			/**
			 * Flag to indicate an ongoing term propagation.
			 * @type {boolean}
			 */
			this.isPropagating = false;
		},

		/**
		 * Propagates the new value of one term select element to all other term select elements.
		 * @param {Event} event - The change event of a term select element.
		 */
		propagateSelectedTerm: function( event ) {
			var $select,
				relation;

			if ( this.isPropagating ) {
				return;
			}

			this.isPropagating = true;

			$select = $( event.target );

			relation = this.getSelectedRelation( $select );
			if ( '' !== relation ) {
				this.$selects.not( $select ).each( function( index, element ) {
					this.selectTerm( $( element ), relation );
				}.bind( this ) );
			}

			this.isPropagating = false;
		},

		/**
		 * Returns the relation of the given select element (i.e., its currently selected option).
		 * @param {jQuery} $select - A select element.
		 * @returns {string} The relation of the selected term.
		 */
		getSelectedRelation: function( $select ) {
			return $select.find( 'option:selected' ).data( 'relation' ) || '';
		},

		/**
		 * Sets the given select element's value to that of the option with the given relation, or the first option.
		 * @param {jQuery} $select - A select element.
		 * @param {string} relation - The relation of a term.
		 */
		selectTerm: function( $select, relation ) {
			var $option = $select.find( 'option[data-relation="' + relation + '"]' );
			if ( $option.length ) {
				$select.val( $option.val() );
			} else if ( this.getSelectedRelation( $select ) ) {
				$select.val( $select.find( 'option' ).first().val() );
			}
		}
	} );

	// Register the TermTranslator module for the Edit Tags admin page.
	MultilingualPress.registerModule( 'edit-tags.php', 'TermTranslator', TermTranslator, {
		el: '#mlp-term-translations',
		events: {
			'change select': 'propagateSelectedTerm'
		}
	} );
})( jQuery, window.MultilingualPress );

(function( MultilingualPress ) {
	'use strict';

	/**
	 * Settings for the MultilingualPress UserBackendLanguage module. Only available on the targeted admin pages.
	 * @type {Object}
	 */
	var moduleSettings = MultilingualPress.getSettings( 'UserBackendLanguage' );

	var UserBackendLanguage = Backbone.View.extend( /** @lends UserBackendLanguage# */ {
		/**
		 * @constructs UserBackendLanguage
		 * @classdesc MultilingualPress UserBackendLanguage module.
		 * @extends Backbone.View
		 */
		initialize: function() {
			this.$el.val( moduleSettings.locale );
		}
	} );

	// Register the UserBackendLanguage module for the General Settings admin page.
	MultilingualPress.registerModule( 'options-general.php', 'UserBackendLanguage', UserBackendLanguage, {
		el: '#WPLANG'
	} );
})( window.MultilingualPress );
