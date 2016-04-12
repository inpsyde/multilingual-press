'use strict';

import * as Util from "./common/utils";
import * as F from "./admin/core/functions";
import { Toggler } from "./admin/core/common";
import Controller from "./admin/core/Controller";
import { EventManager } from "./admin/core/EventManager";
import Model from "./admin/core/Model";
import Registry from "./admin/core/Registry";
import Router from "./admin/core/Router";
import NavMenus from "./admin/nav-menus/NavMenus";
import AddNewSite from "./admin/network/AddNewSite";
import CopyPost from "./admin/post-translation/CopyPost";
import RelationshipControl from "./admin/post-translation/RelationshipControl";
import RemotePostSearch from "./admin/post-translation/RemotePostSearch";
import TermTranslator from "./admin/term-translation/TermTranslator";
import UserBackEndLanguage from "./admin/user-settings/UserBackEndLanguage";
import CopyPostAnimation from "./admin/post-translation/CopyPostAnimation";

const ajaxURL = window.ajaxurl;

/**
 * The MultilingualPress admin namespace.
 * @namespace
 * @alias MultilingualPressAdmin
 */
const MLP = {
	/**
	 * The MultilingualPress admin controller instance.
	 * @type {Controller}
	 */
	controller: new Controller(
		new Registry(
			new Router()
		),
		F.getSettings( 'mlpSettings' )
	),

	/**
	 * The set of core-specific methods.
	 * @type {Object}
	 */
	Functions: F,

	/**
	 * The set of utility methods.
	 * @type {Object}
	 */
	Util
};

const { controller } = MLP;

/**
 * The MultilingualPress toggler instance.
 * @type {Toggler}
 */
const toggler = new Toggler( {
	el: 'body',
	events: {
		'click .mlp-click-toggler': 'toggleElement'
	}
} );

// Initialize the state togglers.
toggler.initializeStateTogglers();

let settings;

// Register the NavMenus module for the Menus admin page.
settings = F.getSettings( NavMenus );
controller.registerModule( 'nav-menus.php', NavMenus, {
	el: '#' + settings.metaBoxID,
	events: {
		'click #submit-mlp-language': 'sendRequest'
	},
	model: new Model( { urlRoot: ajaxURL } ),
	moduleSettings: settings
} );

// Register the AddNewSite module for the Add New Site network admin page.
controller.registerModule( 'network/site-new.php', AddNewSite, {
	el: '#wpbody-content form',
	events: {
		'change #site-language': 'adaptLanguage',
		'change #mlp-base-site-id': 'togglePluginsRow'
	}
} );

// Register the CopyPost module for the Edit Post and Add New Post admin pages.
controller.registerModule( [ 'post.php', 'post-new.php' ], CopyPost, {
	el: '#post-body',
	EventManager,
	events: {
		'click .mlp-copy-post-button': 'copyPostData'
	},
	model: new Model( { urlRoot: ajaxURL } ),
	moduleSettings: F.getSettings( CopyPost )
} );

// Register the CopyPost module for the Edit Post and Add New Post admin pages.
controller.registerModule( [ 'post.php', 'post-new.php' ], CopyPostAnimation, {
	el: '#post-body',
	EventManager,
	events: {
		'CopyPost:copyPostData': 'fadeIn',
		'CopyPost:updatePostData': 'fadeOut'
	},
} );

// Register the RelationshipControl module for the Edit Post and Add New Post admin pages.
controller.registerModule( [ 'post.php', 'post-new.php' ], RelationshipControl, {
	el: '#post-body',
	EventManager,
	events: {
		'change .mlp-rc-actions input': 'updateUnsavedRelationships',
		'click #publish': 'confirmUnsavedRelationships',
		'click .mlp-save-relationship-button': 'saveRelationship'
	},
	moduleSettings: F.getSettings( RelationshipControl ),
	Util
}, ( module ) => module.initializeEventHandlers() );

// Register the RemotePostSearch module for the Edit Post and Add New Post admin pages.
controller.registerModule( [ 'post.php', 'post-new.php' ], RemotePostSearch, {
	el: '#post-body',
	events: {
		'keydown .mlp-search-field': 'preventFormSubmission',
		'keyup .mlp-search-field': 'reactToInput'
	},
	model: new Model( { urlRoot: ajaxURL } ),
	moduleSettings: F.getSettings( RemotePostSearch )
}, ( module ) => module.initializeResults() );

// Register the TermTranslator module for the Edit Tags admin page.
controller.registerModule( 'edit-tags.php', TermTranslator, {
	el: '#mlp-term-translations',
	events: {
		'change select': 'propagateSelectedTerm'
	}
} );

// Register the UserBackEndLanguage module for the General Settings admin page.
controller.registerModule( 'options-general.php', UserBackEndLanguage, {
	el: '#WPLANG',
	moduleSettings: F.getSettings( UserBackEndLanguage )
}, ( module ) => module.updateSiteLanguage() );

// Initialize the admin controller, and thus all modules registered for the current admin page.
jQuery( () => {
	/**
	 * The module instances registered for the current admin page.
	 * @type {Object}
	 */
	MLP.modules = controller.initialize();
} );

// Externalize the MultilingualPress admin namespace.
window.MultilingualPressAdmin = MLP;
