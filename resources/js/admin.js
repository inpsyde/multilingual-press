'use strict';

import * as F from "./admin/core/functions";
import { Toggler } from "./admin/core/common";
import Controller from "./admin/core/Controller";
import Model from "./admin/core/Model";
import NavMenus from "./admin/nav-menus/NavMenus";
import AddNewSite from "./admin/network/AddNewSite";
import RemotePostSearch from "./admin/post-translator/RemotePostSearch";
import TermTranslator from "./admin/term-translation/TermTranslator";
import UserBackEndLanguage from "./admin/user-settings/UserBackEndLanguage";

const ajaxUrl = window.ajaxurl;

/**
 * The MultilingualPress admin namespace.
 * @namespace
 * @alias MultilingualPressAdmin
 */
const MLP = window.MultilingualPressAdmin = {};

const toggler = new Toggler( {
	el: 'body',
	events: {
		'click .mlp-click-toggler': 'toggleElement'
	}
} );
/**
 * The MultilingualPress toggler instance.
 * @type {Toggler}
 */
MLP.toggler = toggler;

// Initialize the state togglers.
toggler.initializeStateTogglers();

const controller = new Controller();
/**
 * The MultilingualPress admin controller instance.
 * @type {Controller}
 */
MLP.controller = controller;

let settings;

// Register the NavMenus module for the Menus admin page.
settings = F.getSettings( NavMenus );
controller.registerModule( 'nav-menus.php', NavMenus, {
	el: '#' + settings.metaBoxID,
	events: {
		'click #submit-mlp-language': 'sendRequest'
	},
	model: new Model( { urlRoot: ajaxUrl } ),
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

// Register the RemotePostSearch module for the Add New Post and the Edit Post admin pages.
settings = F.getSettings( RemotePostSearch );
controller.registerModule( [ 'post.php', 'post-new.php' ], RemotePostSearch, {
	el: 'body',
	events: {
		'keydown .mlp-search-field': 'preventFormSubmission',
		'keyup .mlp-search-field': 'reactToInput'
	},
	model: new Model( { urlRoot: ajaxUrl } ),
	moduleSettings: settings
}, module => module.initializeResults() );

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
}, module => module.updateSiteLanguage() );

// Initialize the admin controller, and thus all modules registered for the current admin page.
jQuery( controller.initialize );
