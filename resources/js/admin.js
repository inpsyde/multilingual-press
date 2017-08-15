import * as Util from './common/utils';
import * as F from './admin/core/functions';
import { Toggler } from './admin/core/common';
import Controller from './admin/core/Controller';
import EventManager from './admin/core/EventManager';
import Model from './admin/core/Model';
import Registry from './admin/core/Registry';
import Router from './admin/core/Router';
import NavMenus from './admin/nav-menus/NavMenus';
import AddNewSite from './admin/network/AddNewSite';
import CopyPost from './admin/post-translation/CopyPost';
import LiveSearch from './admin/post-translation/LiveSearch';
import RelationshipControl from './admin/post-translation/RelationshipControl';
import TermTranslator from './admin/term-translation/TermTranslator';

const { ajaxurl: ajaxUrl, jQuery: $ } = window;

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

// Register the NavMenus module for the Menus admin page.
const navMenusSettings = F.getSettings( 'NavMenus' );
controller.registerModule( 'nav-menus.php', NavMenus, {
	el: `#${navMenusSettings.metaBoxId}`,
	events: {
		'click #submit-mlp-language': 'sendRequest'
	},
	model: new Model( { urlRoot: ajaxUrl } ),
	settings: navMenusSettings
} );

// Register the AddNewSite module for the Add New Site network admin page.
controller.registerModule( 'network/site-new.php', AddNewSite, {
	el: '#wpbody-content',
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
	model: new Model( { urlRoot: ajaxUrl } ),
	settings: F.getSettings( 'CopyPost' )
} );

// Register the LiveSearch module for the Edit Post and Add New Post admin pages.
controller.registerModule( [ 'post.php', 'post-new.php' ], LiveSearch, {
	el: '#post-body',
	events: {
		'keydown .mlp-rc-search': 'preventFormSubmission',
		'keyup .mlp-rc-search': 'reactToInput'
	},
	model: new Model( { urlRoot: ajaxUrl } ),
	settings: F.getSettings( 'LiveSearch' )
}, ( module ) => module.initializeResults() );

// Register the RelationshipControl module for the Edit Post and Add New Post admin pages.
controller.registerModule( [ 'post.php', 'post-new.php' ], RelationshipControl, {
	el: '#post-body',
	EventManager,
	events: {
		'change .mlp-rc-actions input': 'updateUnsavedRelationships',
		'click #publish': 'confirmUnsavedRelationships',
		'click .mlp-save-relationship-button': 'saveRelationship'
	},
	settings: F.getSettings( 'RelationshipControl' ),
	Util
}, ( module ) => module.initializeEventHandlers() );

// Register the TermTranslator module for the Tags and Edit Tag admin page.
controller.registerModule( [ 'edit-tags.php', 'term.php' ], TermTranslator, {
	el: '#wpbody-content',
	events: {
		'change .mlp-term-select': 'propagateSelectedTerm',
		'input .mlp-term-input': 'selectCreateTermOperation'
	}
} );

// Initialize the admin controller, and thus all modules registered for the current admin page.
$( () => {
	/**
	 * The module instances registered for the current admin page.
	 * @type {Object}
	 */
	MLP.modules = controller.initialize();
} );

// Externalize the MultilingualPress admin namespace.
window.MultilingualPressAdmin = MLP;
