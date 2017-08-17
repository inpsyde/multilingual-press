(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var _utils = require("./common/utils");

var Util = _interopRequireWildcard(_utils);

var _functions = require("./admin/core/functions");

var F = _interopRequireWildcard(_functions);

var _common = require("./admin/core/common");

var _Controller = require("./admin/core/Controller");

var _Controller2 = _interopRequireDefault(_Controller);

var _EventManager = require("./admin/core/EventManager");

var _EventManager2 = _interopRequireDefault(_EventManager);

var _Model = require("./admin/core/Model");

var _Model2 = _interopRequireDefault(_Model);

var _Registry = require("./admin/core/Registry");

var _Registry2 = _interopRequireDefault(_Registry);

var _Router = require("./admin/core/Router");

var _Router2 = _interopRequireDefault(_Router);

var _NavMenus = require("./admin/nav-menus/NavMenus");

var _NavMenus2 = _interopRequireDefault(_NavMenus);

var _AddNewSite = require("./admin/network/AddNewSite");

var _AddNewSite2 = _interopRequireDefault(_AddNewSite);

var _CopyPost = require("./admin/post-translation/CopyPost");

var _CopyPost2 = _interopRequireDefault(_CopyPost);

var _RelationshipControl = require("./admin/post-translation/RelationshipControl");

var _RelationshipControl2 = _interopRequireDefault(_RelationshipControl);

var _RemotePostSearch = require("./admin/post-translation/RemotePostSearch");

var _RemotePostSearch2 = _interopRequireDefault(_RemotePostSearch);

var _TermTranslator = require("./admin/term-translation/TermTranslator");

var _TermTranslator2 = _interopRequireDefault(_TermTranslator);

var _UserBackEndLanguage = require("./admin/user-settings/UserBackEndLanguage");

var _UserBackEndLanguage2 = _interopRequireDefault(_UserBackEndLanguage);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } else { var newObj = {}; if (obj != null) { for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) newObj[key] = obj[key]; } } newObj.default = obj; return newObj; } }

var _window = window,
    ajaxurl = _window.ajaxurl,
    jQuery = _window.jQuery;

/**
 * The MultilingualPress admin namespace.
 * @namespace
 * @alias MultilingualPressAdmin
 */

var MLP = {
	/**
  * The MultilingualPress admin controller instance.
  * @type {Controller}
  */
	controller: new _Controller2.default(new _Registry2.default(new _Router2.default()), F.getSettings('mlpSettings')),

	/**
  * The set of core-specific methods.
  * @type {Object}
  */
	Functions: F,

	/**
  * The set of utility methods.
  * @type {Object}
  */
	Util: Util
};

var controller = MLP.controller;

/**
 * The MultilingualPress toggler instance.
 * @type {Toggler}
 */

var toggler = new _common.Toggler({
	el: 'body',
	events: {
		'click .mlp-click-toggler': 'toggleElement'
	}
});

// Initialize the state togglers.
toggler.initializeStateTogglers();

var settings = void 0;

// Register the NavMenus module for the Menus admin page.
settings = F.getSettings('NavMenus');
controller.registerModule('nav-menus.php', _NavMenus2.default, {
	el: '#' + settings.metaBoxID,
	events: {
		'click #submit-mlp-language': 'sendRequest'
	},
	model: new _Model2.default({ urlRoot: ajaxurl }),
	settings: settings
});

// Register the AddNewSite module for the Add New Site network admin page.
controller.registerModule('network/site-new.php', _AddNewSite2.default, {
	el: '#wpbody-content form',
	events: {
		'change #site-language': 'adaptLanguage',
		'change #mlp-base-site-id': 'togglePluginsRow'
	}
});

// Register the CopyPost module for the Edit Post and Add New Post admin pages.
controller.registerModule(['post.php', 'post-new.php'], _CopyPost2.default, {
	el: '#post-body',
	EventManager: _EventManager2.default,
	events: {
		'click .mlp-copy-post-button': 'copyPostData'
	},
	model: new _Model2.default({ urlRoot: ajaxurl }),
	settings: F.getSettings('CopyPost')
});

// Register the RelationshipControl module for the Edit Post and Add New Post admin pages.
controller.registerModule(['post.php', 'post-new.php'], _RelationshipControl2.default, {
	el: '#post-body',
	EventManager: _EventManager2.default,
	events: {
		'change .mlp-rc-actions input': 'updateUnsavedRelationships',
		'click #publish': 'confirmUnsavedRelationships',
		'click .mlp-save-relationship-button': 'saveRelationship'
	},
	settings: F.getSettings('RelationshipControl'),
	Util: Util
}, function (module) {
	return module.initializeEventHandlers();
});

// Register the RemotePostSearch module for the Edit Post and Add New Post admin pages.
controller.registerModule(['post.php', 'post-new.php'], _RemotePostSearch2.default, {
	el: '#post-body',
	events: {
		'keydown .mlp-search-field': 'preventFormSubmission',
		'keyup .mlp-search-field': 'reactToInput'
	},
	model: new _Model2.default({ urlRoot: ajaxurl }),
	settings: F.getSettings('RemotePostSearch')
}, function (module) {
	return module.initializeResults();
});

// Register the TermTranslator module for the Tags and Edit Tag admin page.
controller.registerModule(['edit-tags.php', 'term.php'], _TermTranslator2.default, {
	el: '#mlp-term-translations',
	events: {
		'change select': 'propagateSelectedTerm'
	}
});

// Register the UserBackEndLanguage module for the General Settings admin page.
controller.registerModule('options-general.php', _UserBackEndLanguage2.default, {
	el: '#WPLANG',
	settings: F.getSettings('UserBackEndLanguage')
}, function (module) {
	return module.updateSiteLanguage();
});

// Initialize the admin controller, and thus all modules registered for the current admin page.
jQuery(function () {
	/**
  * The module instances registered for the current admin page.
  * @type {Object}
  */
	MLP.modules = controller.initialize();
});

// Externalize the MultilingualPress admin namespace.
window.MultilingualPressAdmin = MLP;

},{"./admin/core/Controller":2,"./admin/core/EventManager":3,"./admin/core/Model":4,"./admin/core/Registry":5,"./admin/core/Router":6,"./admin/core/common":7,"./admin/core/functions":8,"./admin/nav-menus/NavMenus":9,"./admin/network/AddNewSite":10,"./admin/post-translation/CopyPost":11,"./admin/post-translation/RelationshipControl":12,"./admin/post-translation/RemotePostSearch":13,"./admin/term-translation/TermTranslator":14,"./admin/user-settings/UserBackEndLanguage":15,"./common/utils":16}],2:[function(require,module,exports){
"use strict";

exports.__esModule = true;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

// Internal pseudo-namespace for private data.
// NOTE: _this is shared between ALL instances of this module! So far, there is only one instance, so no problem NOW.
var _this = {};

/**
 * The MultilingualPress admin controller.
 */

var Controller = function () {
	/**
  * Constructor. Sets up the properties.
  * @param {Registry} registry - The registry object.
  * @param {Object} settings - The controller settings.
  */
	function Controller(registry, settings) {
		_classCallCheck(this, Controller);

		/**
   * The registry object.
   * @type {Registry}
   */
		_this.registry = registry;

		/**
   * The controller settings.
   * @type {Object}
   */
		_this.settings = settings;
	}

	/**
  * Returns the settings object.
  * @returns {Object} The settings object.
  */


	/**
  * Initializes the instance.
  * @returns {Object} The module instances registered for the current admin page.
  */
	Controller.prototype.initialize = function initialize() {
		var modules = _this.registry.initializeRoutes();

		this.maybeStartHistory();

		return modules;
	};

	/**
  * Starts Backbone's history, unless it has been started already.
  * @returns {Boolean} Whether or not the history has been started right now.
  */


	Controller.prototype.maybeStartHistory = function maybeStartHistory() {
		if (Backbone.History.started) {
			return false;
		}

		Backbone.history.start({
			root: this.settings.urlRoot,
			pushState: true,
			hashChange: false
		});

		return true;
	};

	/**
  * Registers a new module with the given Module callback under the given name for the given routes.
  * @param {String|String[]} routes - One or more routes.
  * @param {Function} Constructor - The constructor callback for the module.
  * @param {Object} [options={}] - Optional. The options for the module. Default to an empty object.
  * @param {Function} [callback=null] - Optional. The callback to execute after construction. Defaults to null.
  */


	Controller.prototype.registerModule = function registerModule(routes, Constructor) {
		var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
		var callback = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;

		var moduleData = {
			Constructor: Constructor,
			options: options,
			callback: callback
		};

		if (!Array.isArray(routes)) {
			routes = [routes];
		}

		routes.forEach(function (route) {
			return _this.registry.registerModuleForRoute(moduleData, route);
		});
	};

	_createClass(Controller, [{
		key: "settings",
		get: function get() {
			return _this.settings;
		}
	}]);

	return Controller;
}();

exports.default = Controller;

},{}],3:[function(require,module,exports){
"use strict";

exports.__esModule = true;
/**
 * The MultilingualPress EventManager module.
 */
exports.default = window._.extend({}, Backbone.Events);

},{}],4:[function(require,module,exports){
"use strict";

exports.__esModule = true;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * The MultilingualPress Model module.
 */
var Model = function (_Backbone$Model) {
	_inherits(Model, _Backbone$Model);

	/**
  * Constructor. Sets up the properties.
  * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
  */
	function Model() {
		var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

		_classCallCheck(this, Model);

		/**
   * The URL root.
   * @type {String}
   */
		var _this = _possibleConstructorReturn(this, _Backbone$Model.call(this, options));

		_this.urlRoot = options.urlRoot;
		return _this;
	}

	return Model;
}(Backbone.Model);

exports.default = Model;

},{}],5:[function(require,module,exports){
'use strict';

exports.__esModule = true;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var $ = window.jQuery;

// Internal pseudo-namespace for private data.
// NOTE: _this is shared between ALL instances of this module! So far, there is only one instance, so no problem NOW.
var _this = {
	/**
  * The registry data (i.e., module-per-route).
  * @type {Object}
  */
	data: {},

	/**
  * The module instances registered for the current admin page.
  * @type {Object}
  */
	modules: {}
};

/**
 * The MultilingualPress Registry module.
 */

var Registry = function () {
	/**
  * Constructor. Sets up the properties.
  * @param {Router} router - The router object.
  */
	function Registry(router) {
		_classCallCheck(this, Registry);

		/**
   * The router object.
   * @type {Router}
   */
		_this.router = router;
	}

	/**
  * Creates and stores the module instance for the given module data.
  * @param {Object} data - The module data.
  * @returns {Object} The module instance.
  */


	Registry.prototype.createModule = function createModule(data) {
		var Constructor = data.Constructor,
		    module = new Constructor(data.options || {});

		_this.modules[Constructor.name] = module;

		if ('function' === typeof data.callback) {
			data.callback(module);
		}

		return module;
	};

	/**
  * Creates and stores the module instances for the given modules data.
  * @param {Object[]} modules - The modules data.
  */


	Registry.prototype.createModules = function createModules(modules) {
		var _this2 = this;

		$.each(modules, function (module, data) {
			return _this2.createModule(data);
		});
	};

	/**
  * Initializes the given route.
  * @param {String} route - The route.
  * @param {Object[]} modules - The modules data.
  */


	Registry.prototype.initializeRoute = function initializeRoute(route, modules) {
		var _this3 = this;

		_this.router.route(route, route, function () {
			return _this3.createModules(modules);
		});
	};

	/**
  * Sets up all routes with the according registered modules.
  * @returns {Object} The module instances registered for the current admin page.
  */


	Registry.prototype.initializeRoutes = function initializeRoutes() {
		var _this4 = this;

		$.each(_this.data, function (route, modules) {
			return _this4.initializeRoute(route, modules);
		});

		return _this.modules;
	};

	/**
  * Registers the module with the given data for the given route.
  * @param {Object} module - The module data.
  * @param {String} route - The route.
  * @returns {Number} The number of the currently registered routes.
  */


	Registry.prototype.registerModuleForRoute = function registerModuleForRoute(module, route) {
		if (!_this.data[route]) {
			_this.data[route] = [];
		}

		return _this.data[route].push(module);
	};

	return Registry;
}();

exports.default = Registry;

},{}],6:[function(require,module,exports){
"use strict";

exports.__esModule = true;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * The MultilingualPress Router module.
 */
var Router = function (_Backbone$Router) {
	_inherits(Router, _Backbone$Router);

	/**
  * Constructor. Sets up the properties.
  * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
  */
	function Router() {
		var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

		_classCallCheck(this, Router);

		return _possibleConstructorReturn(this, _Backbone$Router.call(this, options));
	}

	return Router;
}(Backbone.Router);

exports.default = Router;

},{}],7:[function(require,module,exports){
'use strict';

exports.__esModule = true;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var $ = window.jQuery;

/**
 * The MultilingualPress Toggler module.
 */

var Toggler = exports.Toggler = function (_Backbone$View) {
	_inherits(Toggler, _Backbone$View);

	/**
  * Constructor. Sets up the properties.
  * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
  */
	function Toggler() {
		var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

		_classCallCheck(this, Toggler);

		return _possibleConstructorReturn(this, _Backbone$View.call(this, options));
	}

	/**
  * Initializes the given toggler that works by using its individual state.
  * @param {jQuery} $toggler - The jQuery representation of a toggler element.
  */


	Toggler.prototype.initializeStateToggler = function initializeStateToggler($toggler) {
		$('[name="' + $toggler.attr('name') + '"]').on('change', {
			$toggler: $toggler
		}, this.toggleElementIfChecked);
	};

	/**
  * Initializes the togglers that work by using their individual state.
  */


	Toggler.prototype.initializeStateTogglers = function initializeStateTogglers() {
		var _this2 = this;

		$('.mlp-state-toggler').each(function (index, element) {
			return _this2.initializeStateToggler($(element));
		});
	};

	/**
  * Toggles the element with the ID given in the according data attribute.
  * @param {Event} event - The click event of a toggler element.
  */


	Toggler.prototype.toggleElement = function toggleElement(event) {
		var targetID = $(event.target).data('toggle-target');

		if (targetID) {
			$(targetID).toggle();
		}
	};

	/**
  * Toggles the element with the ID given in the according toggler's data attribute if the toggler is checked.
  * @param {Event} event - The change event of an input element.
  */


	Toggler.prototype.toggleElementIfChecked = function toggleElementIfChecked(event) {
		var $toggler = event.data.$toggler,
		    targetID = $toggler.data('toggle-target');

		if (targetID) {
			$(targetID).toggle($toggler.is(':checked'));
		}
	};

	return Toggler;
}(Backbone.View);

},{}],8:[function(require,module,exports){
'use strict';

exports.__esModule = true;

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

/**
 * Returns the name of the given module.
 * @param {Function|String|Object} module - The instance or constructor or name of a MulitilingualPress module.
 * @returns {String} The name of the module.
 */
var getModuleName = function getModuleName(module) {
	switch (typeof module === 'undefined' ? 'undefined' : _typeof(module)) {
		case 'function':
			return module.name;

		case 'string':
			return module;

		case 'object':
			return module.constructor.name;
	}

	return '';
};

/**
 * Returns the settings for the given module or settings name.
 * @param {Function|String|Object} module - The instance or constructor or name of a MulitilingualPress module.
 * @returns {Object} The settings.
 */
var getSettings = exports.getSettings = function getSettings(module) {
	module = getModuleName(module);

	if ('undefined' !== typeof window['mlp' + module + 'Settings']) {
		return window['mlp' + module + 'Settings'];
	}

	if ('undefined' !== typeof window[module]) {
		return window[module];
	}

	return {};
};

},{}],9:[function(require,module,exports){
'use strict';

exports.__esModule = true;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var $ = window.jQuery;

// Internal pseudo-namespace for private data.
// NOTE: _this is shared between ALL instances of this module! So far, there is only one instance, so no problem NOW.
var _this = {};

/**
 * The MultilingualPress NavMenus module.
 */

var NavMenus = function (_Backbone$View) {
	_inherits(NavMenus, _Backbone$View);

	/**
  * Constructor. Sets up the properties.
  * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
  */
	function NavMenus() {
		var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

		_classCallCheck(this, NavMenus);

		/**
   * The jQuery object representing the MultilingualPress language checkboxes.
   * @type {jQuery}
   */
		var _this2 = _possibleConstructorReturn(this, _Backbone$View.call(this, options));

		_this.$languages = _this2.$el.find('li [type="checkbox"]');

		/**
   * The jQuery object representing the input element that contains the currently edited menu's ID.
   * @type {jQuery}
   */
		_this.$menu = $('#menu');

		/**
   * The jQuery object representing the currently edited menu.
   * @type {jQuery}
   */
		_this.$menuToEdit = $('#menu-to-edit');

		/**
   * The jQuery object representing the Languages meta box spinner.
   * @type {jQuery}
   */
		_this.$spinner = _this2.$el.find('.spinner');

		/**
   * The jQuery object representing the Languages meta box submit button.
   * @type {jQuery}
   */
		_this.$submit = _this2.$el.find('#submit-mlp-language');

		/**
   * The settings.
   * @type {Object}
   */
		_this.settings = options.settings;

		_this2.listenTo(_this2.model, 'change', _this2.render);
		return _this2;
	}

	/**
  * Returns the settings.
  * @returns {Object} The settings.
  */


	/**
  * Requests the according markup for the checked languages in the Languages meta box.
  * @param {Event} event - The click event of the submit button.
  */
	NavMenus.prototype.sendRequest = function sendRequest(event) {
		var data = {
			action: this.settings.action,
			menu: _this.$menu.val(),
			mlp_sites: this.getSiteIDs()
		};
		data[this.settings.nonceName] = this.settings.nonce;

		event.preventDefault();

		_this.$submit.prop('disabled', true);

		_this.$spinner.addClass('is-active');

		this.model.fetch({
			data: data,
			processData: true
		});
	};

	/**
  * Returns the site IDs for the checked languages in the Languages meta box.
  * @returns {Number[]} The site IDs.
  */


	NavMenus.prototype.getSiteIDs = function getSiteIDs() {
		var ids = [];

		_this.$languages.filter(':checked').each(function (i, element) {
			return ids.push(Number($(element).val() || 0));
		});

		return ids;
	};

	/**
  * Renders the nav menu item to the currently edited menu.
  * @returns {Boolean} Whether or not the nav menu item was rendered.
  */


	NavMenus.prototype.render = function render() {
		var success = this.model.get('success');

		if (success) {
			_this.$menuToEdit.append(this.model.get('data'));
		}

		_this.$languages.prop('checked', false);

		_this.$spinner.removeClass('is-active');

		_this.$submit.prop('disabled', false);

		return success;
	};

	_createClass(NavMenus, [{
		key: 'settings',
		get: function get() {
			return _this.settings;
		}
	}]);

	return NavMenus;
}(Backbone.View);

exports.default = NavMenus;

},{}],10:[function(require,module,exports){
'use strict';

exports.__esModule = true;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var $ = window.jQuery;
var _window = window,
    _ = _window._;

// Internal pseudo-namespace for private data.
// NOTE: _this is shared between ALL instances of this module! So far, there is only one instance, so no problem NOW.

var _this = {};

/**
 * MultilingualPress AddNewSite module.
 */

var AddNewSite = function (_Backbone$View) {
	_inherits(AddNewSite, _Backbone$View);

	/**
  * Constructor. Sets up the properties.
  * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
  */
	function AddNewSite() {
		var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

		_classCallCheck(this, AddNewSite);

		/**
   * As of WordPress 4.5.0, there is now an appropriate action hook on the Add New Site network admin page.
   * Due to our BC policy, we have to wait for WordPress 4.5.0 + 2 in order to make use of it, though.
   * TODO: Remove the following (and adapt the according PHP parts) with the release of WordPress 4.5.0 + 2.
   */
		var _this2 = _possibleConstructorReturn(this, _Backbone$View.call(this, options));

		var markup = $('#mlp-add-new-site-template').html() || '';
		if ('' !== markup) {
			// FIRST render the template, THEN set up the properties using elements that just got injected into the DOM.
			_this2.$el.find('.submit').before(_.template(markup)());
		}

		/**
   * The jQuery object representing the MultilingualPress language select.
   * @type {jQuery}
   */
		_this.$language = $('#mlp-site-language');

		/**
   * The jQuery object representing the table row that contains the plugin activation checkbox.
   * @type {jQuery}
   */
		_this.$pluginsRow = $('#mlp-activate-plugins').closest('tr');
		return _this2;
	}

	/**
  * Sets MultilingualPress's language select to the currently selected site language.
  * @param {Event} event - The change event of the site language select element.
  */


	AddNewSite.prototype.adaptLanguage = function adaptLanguage(event) {
		var language = this.getLanguage($(event.target));

		if (_this.$language.find('[value="' + language + '"]').length) {
			_this.$language.val(language);
		}
	};

	/**
  * Returns the selected language of the given select element.
  * @param {jQuery} $select - A select element.
  * @returns {String} The selected language.
  */


	AddNewSite.prototype.getLanguage = function getLanguage($select) {
		var language = $select.val();

		if (language) {
			return language.replace('_', '-');
		}

		return 'en-US';
	};

	/**
  * Toggles the Plugins row according to the source site ID select element's value.
  * @param {Event} event - The change event of the source site ID select element.
  */


	AddNewSite.prototype.togglePluginsRow = function togglePluginsRow(event) {
		_this.$pluginsRow.toggle(0 < $(event.target).val());
	};

	return AddNewSite;
}(Backbone.View);

exports.default = AddNewSite;

},{}],11:[function(require,module,exports){
'use strict';

exports.__esModule = true;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var $ = window.jQuery;
var _window = window,
    _ = _window._;

// Internal pseudo-namespace for private data.
// NOTE: _this is shared between ALL instances of this module! So far, there is only one instance, so no problem NOW.

var _this = {};

/**
 * The MultilingualPress CopyPost module.
 */

var CopyPost = function (_Backbone$View) {
	_inherits(CopyPost, _Backbone$View);

	/**
  * Constructor. Sets up the properties.
  * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
  */
	function CopyPost() {
		var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

		_classCallCheck(this, CopyPost);

		/**
   * The jQuery object representing the input element that contains the currently edited post's content.
   * @type {jQuery}
   */
		var _this2 = _possibleConstructorReturn(this, _Backbone$View.call(this, options));

		_this.$content = $('#content');

		/**
   * The jQuery object representing the input element that contains the currently edited post's excerpt.
   * @type {jQuery}
   */
		_this.$excerpt = $('#excerpt');

		/**
   * The jQuery object representing the input element that contains the currently edited post's title.
   * @type {jQuery}
   */
		_this.$title = $('#title');

		/**
   * The event manager object.
   * @type {EventManager}
   */
		_this.EventManager = options.EventManager;

		/**
   * The currently edited post's ID.
   * @type {Number}
   */
		_this.postID = Number($('#post_ID').val() || 0);

		/**
   * The settings.
   * @type {Object}
   */
		_this.settings = options.settings;

		_this2.listenTo(_this2.model, 'change:data', _this2.updatePostData);
		return _this2;
	}

	/**
  * Returns the content of the original post.
  * @returns {String} The post content.
  */


	/**
  * Copies the post data of the source post to a translation post.
  * @param {Event} event - The click event of a "Copy source post" button.
  */
	CopyPost.prototype.copyPostData = function copyPostData(event) {
		var remoteSiteID = this.getRemoteSiteID($(event.target));

		var data = {};

		event.preventDefault();

		this.fadeOutMetaBox(remoteSiteID);

		$('#mlp-translation-data-' + remoteSiteID + '-copied-post').val(1);

		/**
   * Triggers the event before copying post data, and passes an object for adding custom data, and the current
   * site and post IDs and the remote site ID.
   */
		_this.EventManager.trigger('CopyPost:copyPostData', data, this.settings.siteID, this.postID, remoteSiteID);

		data = _.extend(data, {
			action: this.settings.action,
			current_post_id: this.postID,
			remote_site_id: remoteSiteID,
			title: this.title,
			slug: this.slug,
			content: this.content,
			tinyMCEContent: this.tinyMCEContent,
			excerpt: this.excerpt
		});

		this.model.save(data, {
			data: data,
			processData: true
		});
	};

	/**
  * Returns the site ID data attribute value of the given "Copy source post" button.
  * @param {jQuery} $button - A "Copy source post" button.
  * @returns {Number} The site ID.
  */


	CopyPost.prototype.getRemoteSiteID = function getRemoteSiteID($button) {
		return Number($button.data('site-id') || 0);
	};

	/**
  * Fades the meta box out.
  * @param {Number} remoteSiteID - The remote site ID.
  */


	CopyPost.prototype.fadeOutMetaBox = function fadeOutMetaBox(remoteSiteID) {
		$('#inpsyde_multilingual_' + remoteSiteID).css('opacity', .4);
	};

	/**
  * Updates the post data in the according meta box for the given site ID.
  * @returns {Boolean} Whether or not the post data have been updated.
  */


	CopyPost.prototype.updatePostData = function updatePostData() {
		var data = void 0,
		    prefix = void 0;

		if (!this.model.get('success')) {
			return false;
		}

		data = this.model.get('data');

		prefix = 'mlp-translation-data-' + data.siteID + '-';

		$('#' + prefix + 'title').val(data.title);

		$('#' + prefix + 'name').val(data.slug);

		this.setTinyMCEContent(prefix + 'content', data.tinyMCEContent);

		$('#' + prefix + 'content').val(data.content);

		$('#' + prefix + 'excerpt').val(data.excerpt);

		/**
   * Triggers the event for updating the post, and passes the according data.
   */
		_this.EventManager.trigger('CopyPost:updatePostData', data);

		this.fadeInMetaBox(data.siteID);

		return true;
	};

	/**
  * Sets the given content for the tinyMCE editor with the given ID.
  * @param {String} editorID - The tinyMCE editor's ID.
  * @param {String} content - The content.
  * @returns {Boolean} Whether or not the post content has been updated.
  */


	CopyPost.prototype.setTinyMCEContent = function setTinyMCEContent(editorID, content) {
		var editor = void 0;

		if ('undefined' === typeof window.tinyMCE) {
			return false;
		}

		editor = window.tinyMCE.get(editorID);
		if (!editor) {
			return false;
		}

		editor.setContent(content);

		return true;
	};

	/**
  * Fades the meta box in.
  * @param {Number} remoteSiteID - The remote site ID.
  */


	CopyPost.prototype.fadeInMetaBox = function fadeInMetaBox(remoteSiteID) {
		$('#inpsyde_multilingual_' + remoteSiteID).css('opacity', 1);
	};

	_createClass(CopyPost, [{
		key: 'content',
		get: function get() {
			return _this.$content.val() || '';
		}

		/**
   * Returns the excerpt of the original post.
   * @returns {String} The post excerpt.
   */

	}, {
		key: 'excerpt',
		get: function get() {
			return _this.$excerpt.val() || '';
		}

		/**
   * Returns the currently edited post's ID.
   * @returns {Number} The currently edited post's ID.
   */

	}, {
		key: 'postID',
		get: function get() {
			return _this.postID;
		}

		/**
   * Returns the settings.
   * @returns {Object} The settings.
   */

	}, {
		key: 'settings',
		get: function get() {
			return _this.settings;
		}

		/**
   * Returns the slug of the original post.
   * @returns {String} The post slug.
   */

	}, {
		key: 'slug',
		get: function get() {
			// Since editing the permalink replaces the "edit slug box" markup, the slug DOM element cannot be cached.
			return $('#editable-post-name-full').text() || '';
		}

		/**
   * Returns the TinyMCE content of the original post.
   * @returns {string} The post content.
   */

	}, {
		key: 'tinyMCEContent',
		get: function get() {
			if ('undefined' !== typeof window.tinyMCE) {
				/**
     * The TinyMCE instance of the currently edited post's visual editor.
     * @type {Object}
     */
				_this.tinyMCE = window.tinyMCE.get('content');
			}

			return _this.tinyMCE ? _this.tinyMCE.getContent() : '';
		}

		/**
   * Returns the title of the original post.
   * @returns {String} The post title.
   */

	}, {
		key: 'title',
		get: function get() {
			return _this.$title.val() || '';
		}
	}]);

	return CopyPost;
}(Backbone.View);

exports.default = CopyPost;

},{}],12:[function(require,module,exports){
'use strict';

exports.__esModule = true;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var $ = window.jQuery;

// Internal pseudo-namespace for private data.
// NOTE: _this is shared between ALL instances of this module! So far, there is only one instance, so no problem NOW.
var _this = {
	/**
  * Array of jQuery objects representing meta boxes with unsaved relationships.
  * @type {jQuery[]}
  */
	unsavedRelationships: []
};

/**
 * The MultilingualPress RelationshipControl module.
 */

var RelationshipControl = function (_Backbone$View) {
	_inherits(RelationshipControl, _Backbone$View);

	/**
  * Constructor. Sets up the properties.
  * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
  */
	function RelationshipControl() {
		var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

		_classCallCheck(this, RelationshipControl);

		/**
   * The event manager object.
   * @type {EventManager}
   */
		var _this2 = _possibleConstructorReturn(this, _Backbone$View.call(this, options));

		_this.EventManager = options.EventManager;

		/**
   * The settings.
   * @type {Object}
   */
		_this.settings = options.settings;

		/**
   * The set of utility methods.
   * @type {Object}
   */
		_this.Util = options.Util;
		return _this2;
	}

	/**
  * Returns the settings.
  * @returns {Object} The settings.
  */


	/**
  * Initializes the event handlers for all custom relationship control events.
  */
	RelationshipControl.prototype.initializeEventHandlers = function initializeEventHandlers() {
		_this.EventManager.on({
			'RelationshipControl:connectExistingPost': this.connectExistingPost,
			'RelationshipControl:connectNewPost': this.connectNewPost,
			'RelationshipControl:disconnectPost': this.disconnectPost
		}, this);
	};

	/**
  * Updates the unsaved relationships array for the meta box containing the changed radio input element.
  * @param {Event} event - The change event of a radio input element.
  * @returns {jQuery[]} The array of jQuery objects representing meta boxes with unsaved relationships.
  */


	RelationshipControl.prototype.updateUnsavedRelationships = function updateUnsavedRelationships(event) {
		var $input = $(event.target),
		    $metaBox = $input.closest('.mlp-translation-meta-box'),
		    $button = $metaBox.find('.mlp-save-relationship-button'),
		    index = this.findMetaBox($metaBox);

		if ('stay' === $input.val()) {
			$button.prop('disabled', 'disabled');

			if (-1 !== index) {
				_this.unsavedRelationships.splice(index, 1);
			}
		} else if (-1 === index) {
			_this.unsavedRelationships.push($metaBox);

			$button.removeAttr('disabled');
		}

		return _this.unsavedRelationships;
	};

	/**
  * Returns the index of the given meta box in the unsaved relationships array, and -1 if not found.
  * @param {jQuery} $metaBox - The meta box element.
  * @returns {Number} The index of the meta box.
  */


	RelationshipControl.prototype.findMetaBox = function findMetaBox($metaBox) {
		// By using a for-loop here, one can return early.
		for (var i = 0; i < _this.unsavedRelationships.length; i++) {
			if (_this.unsavedRelationships[i] === $metaBox) {
				return i;
			}
		}

		return -1;
	};

	/**
  * Displays a confirm dialog informing the user about unsaved relationships, if any.
  * @param {Event} event - The click event of the publish button.
  */


	RelationshipControl.prototype.confirmUnsavedRelationships = function confirmUnsavedRelationships(event) {
		if (_this.unsavedRelationships.length && !window.confirm(this.settings.L10n.unsavedRelationships)) {
			event.preventDefault();
		}
	};

	/**
  * Triggers the according event in case of changed relationships.
  * @param {Event} event - The click event of a save relationship button.
  */


	RelationshipControl.prototype.saveRelationship = function saveRelationship(event) {
		var $button = $(event.target),
		    remoteSiteID = $button.data('remote-site-id'),
		    action = $('input[name="mlp-rc-action[' + remoteSiteID + ']"]:checked').val(),
		    eventName = this.getEventName(action);

		if ('stay' === action) {
			return;
		}

		$button.prop('disabled', 'disabled');

		/**
   * Triggers the according event for the current relationship action, and passes data and the event's name.
   */
		_this.EventManager.trigger('RelationshipControl:' + eventName, {
			action: 'mlp_rc_' + action,
			remote_site_id: remoteSiteID,
			remote_post_id: $button.data('remote-post-id'),
			source_site_id: $button.data('source-site-id'),
			source_post_id: $button.data('source-post-id')
		}, eventName);
	};

	/**
  * Returns the according event name for the given relationship action.
  * @param {String} action - The relationship action.
  * @returns {String} The event name.
  */


	RelationshipControl.prototype.getEventName = function getEventName(action) {
		switch (action) {
			case 'search':
				return 'connectExistingPost';

			case 'new':
				return 'connectNewPost';

			case 'disconnect':
				return 'disconnectPost';
		}

		return '';
	};

	/**
  * Handles changing a post's relationship by connecting a new post.
  * @param {Object} data - The common data for all relationship requests.
  */


	RelationshipControl.prototype.connectNewPost = function connectNewPost(data) {
		data.new_post_title = $('input[name="post_title"]').val();

		this.sendRequest(data);
	};

	/**
  * Handles changing a post's relationship by disconnecting the currently connected post.
  * @param {Object} data - The common data for all relationship requests.
  */


	RelationshipControl.prototype.disconnectPost = function disconnectPost(data) {
		this.sendRequest(data);
	};

	/**
  * Handles changing a post's relationship by connecting an existing post.
  * @param {Object} data - The common data for all relationship requests.
  * @returns {Boolean} Whether or not the request has been sent.
  */


	RelationshipControl.prototype.connectExistingPost = function connectExistingPost(data) {
		var remotePostID = Number($('input[name="mlp_add_post[' + data.remote_site_id + ']"]:checked').val() || 0);

		if (!remotePostID) {
			window.alert(this.settings.L10n.noPostSelected);

			return false;
		}

		data.remote_post_id = remotePostID;

		this.sendRequest(data);

		return true;
	};

	/**
  * Changes a post's relationhip by sending a synchronous AJAX request with the according new relationship data.
  * @param {Object} data - The relationship data.
  */


	RelationshipControl.prototype.sendRequest = function sendRequest(data) {
		$.ajax({
			type: 'POST',
			url: window.ajaxurl,
			data: data,
			success: _this.Util.reloadLocation,
			async: false
		});
	};

	_createClass(RelationshipControl, [{
		key: 'settings',
		get: function get() {
			return _this.settings;
		}
	}]);

	return RelationshipControl;
}(Backbone.View);

exports.default = RelationshipControl;

},{}],13:[function(require,module,exports){
'use strict';

exports.__esModule = true;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var $ = window.jQuery;

// Internal pseudo-namespace for private data.
// NOTE: _this is shared between ALL instances of this module! So far, there is only one instance, so no problem NOW.
var _this = {
	/**
  * Array holding the default search result HTML strings.
  * @type {String[]}
  */
	defaultResults: [],

	/**
  * Array holding jQuery objects representing the search result containers.
  * @type {jQuery[]}
  */
	resultsContainers: []
};

/**
 * The MultilingualPress RemotePostSearch module.
 */

var RemotePostSearch = function (_Backbone$View) {
	_inherits(RemotePostSearch, _Backbone$View);

	/**
  * Constructor. Sets up the properties.
  * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
  */
	function RemotePostSearch() {
		var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

		_classCallCheck(this, RemotePostSearch);

		/**
   * The settings.
   * @type {Object}
   */
		var _this2 = _possibleConstructorReturn(this, _Backbone$View.call(this, options));

		_this.settings = options.settings;

		/**
   * Minimum number of characters required to fire the remote post search.
   * @type {Number}
   */
		_this.threshold = parseInt(options.settings.threshold, 10) || 3;

		_this2.listenTo(_this2.model, 'change', _this2.render);
		return _this2;
	}

	/**
  * Returns the settings.
  * @returns {Object} The settings.
  */


	/**
  * Initializes both the default search result view as well as the result container for the given element.
  * @param {HTMLElement} element - The HTML element.
  */
	RemotePostSearch.prototype.initializeResult = function initializeResult(element) {
		var $element = $(element),
		    $resultsContainer = $('#' + $element.data('results-container-id')),
		    siteID = $element.data('remote-site-id');

		_this.defaultResults[siteID] = $resultsContainer.html();
		_this.resultsContainers[siteID] = $resultsContainer;
	};

	/**
  * Initializes both the default search result views as well as the result containers.
  */


	RemotePostSearch.prototype.initializeResults = function initializeResults() {
		var _this3 = this;

		$('.mlp-search-field').each(function (index, element) {
			return _this3.initializeResult(element);
		});
	};

	/**
  * Prevents form submission due to the enter key being pressed.
  * @param {Event} event - The keydown event of a post search element.
  */


	RemotePostSearch.prototype.preventFormSubmission = function preventFormSubmission(event) {
		if (13 === event.which) {
			event.preventDefault();
		}
	};

	/**
  * According to the user input, either search for posts, or display the initial post selection.
  * @param {Event} event - The keyup event of a post search element.
  */


	RemotePostSearch.prototype.reactToInput = function reactToInput(event) {
		var _this4 = this;

		var $input = $(event.target),
		    value = $.trim($input.val() || '');

		var remoteSiteID = void 0;

		if (value === $input.data('value')) {
			return;
		}

		clearTimeout(this.reactToInputTimer);

		$input.data('value', value);

		remoteSiteID = $input.data('remote-site-id');

		if ('' === value) {
			_this.resultsContainers[remoteSiteID].html(_this.defaultResults[remoteSiteID]);
		} else if (value.length >= _this.threshold) {
			this.reactToInputTimer = setTimeout(function () {
				_this4.model.fetch({
					data: {
						action: 'mlp_rc_remote_post_search',
						remote_site_id: remoteSiteID,
						remote_post_id: $input.data('remote-post-id'),
						source_site_id: $input.data('source-site-id'),
						source_post_id: $input.data('source-post-id'),
						s: value
					},
					processData: true
				});
			}, 400);
		}
	};

	/**
  * Renders the found posts to the according results container.
  * @returns {Boolean} Whether or not new data has been rendered.
  */


	RemotePostSearch.prototype.render = function render() {
		var data = void 0;

		if (this.model.get('success')) {
			data = this.model.get('data');

			if (_this.resultsContainers[data.remoteSiteID]) {
				_this.resultsContainers[data.remoteSiteID].html(data.html);
			}

			return true;
		}

		return false;
	};

	_createClass(RemotePostSearch, [{
		key: 'settings',
		get: function get() {
			return _this.settings;
		}
	}]);

	return RemotePostSearch;
}(Backbone.View);

exports.default = RemotePostSearch;

},{}],14:[function(require,module,exports){
'use strict';

exports.__esModule = true;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var $ = window.jQuery;

// Internal pseudo-namespace for private data.
// NOTE: _this is shared between ALL instances of this module! So far, there is only one instance, so no problem NOW.
var _this = {
	/**
  * Flag to indicate an ongoing term propagation.
  * @type {Boolean}
  */
	isPropagating: false
};

/**
 * MultilingualPress TermTranslator module.
 */

var TermTranslator = function (_Backbone$View) {
	_inherits(TermTranslator, _Backbone$View);

	/**
  * Constructor. Sets up the properties.
  * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
  */
	function TermTranslator() {
		var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

		_classCallCheck(this, TermTranslator);

		/**
   * The jQuery object representing the MultilingualPress term selects.
   * @type {jQuery}
   */
		var _this2 = _possibleConstructorReturn(this, _Backbone$View.call(this, options));

		_this.$selects = _this2.$el.find('select');
		return _this2;
	}

	/**
  * Returns the jQuery object representing the MultilingualPress term selects.
  * @returns {jQuery}
  */


	/**
  * Propagates the new value of one term select element to all other term select elements.
  * @param {Event} event - The change event of a term select element.
  */
	TermTranslator.prototype.propagateSelectedTerm = function propagateSelectedTerm(event) {
		var _this3 = this;

		var $select = void 0,
		    relation = void 0;

		if (_this.isPropagating) {
			return;
		}

		_this.isPropagating = true;

		$select = $(event.target);

		relation = this.getSelectedRelation($select);
		if ('' !== relation) {
			this.$selects.not($select).each(function (index, element) {
				return _this3.selectTerm($(element), relation);
			});
		}

		_this.isPropagating = false;
	};

	/**
  * Returns the relation of the given select element (i.e., its currently selected option).
  * @param {jQuery} $select - A select element.
  * @returns {String} The relation of the selected term.
  */


	TermTranslator.prototype.getSelectedRelation = function getSelectedRelation($select) {
		return $select.find('option:selected').data('relation') || '';
	};

	/**
  * Sets the given select element's value to that of the option with the given relation, or the first option.
  * @param {jQuery} $select - A select element.
  * @param {String} relation - The relation of a term.
  * @returns {Boolean} Whether or not a term was selected.
  */


	TermTranslator.prototype.selectTerm = function selectTerm($select, relation) {
		var $option = $select.find('option[data-relation="' + relation + '"]');

		if ($option.length) {
			$select.val($option.val());

			return true;
		} else if (this.getSelectedRelation($select)) {
			$select.val($select.find('option').first().val());

			return true;
		}

		return false;
	};

	_createClass(TermTranslator, [{
		key: '$selects',
		get: function get() {
			return _this.$selects;
		}
	}]);

	return TermTranslator;
}(Backbone.View);

exports.default = TermTranslator;

},{}],15:[function(require,module,exports){
"use strict";

exports.__esModule = true;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

// Internal pseudo-namespace for private data.
// NOTE: _this is shared between ALL instances of this module! So far, there is only one instance, so no problem NOW.
var _this = {};

/**
 * MultilingualPress UserBackEndLanguage module.
 */

var UserBackEndLanguage = function (_Backbone$View) {
	_inherits(UserBackEndLanguage, _Backbone$View);

	/**
  * Constructor. Sets up the properties.
  * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
  */
	function UserBackEndLanguage() {
		var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

		_classCallCheck(this, UserBackEndLanguage);

		/**
   * The settings.
   * @type {Object}
   */
		var _this2 = _possibleConstructorReturn(this, _Backbone$View.call(this, options));

		_this.settings = options.settings;
		return _this2;
	}

	/**
  * Returns the settings.
  * @returns {Object} The settings.
  */


	/**
  * Sets the Site Language value to what it should be.
  */
	UserBackEndLanguage.prototype.updateSiteLanguage = function updateSiteLanguage() {
		this.$el.val(this.settings.locale);
	};

	_createClass(UserBackEndLanguage, [{
		key: "settings",
		get: function get() {
			return _this.settings;
		}
	}]);

	return UserBackEndLanguage;
}(Backbone.View);

exports.default = UserBackEndLanguage;

},{}],16:[function(require,module,exports){
'use strict';

exports.__esModule = true;
/**
 * Attaches the given listener to the given DOM element for the event with the given type.
 * @param {Element} $element - The DOM element.
 * @param {String} type - The type of the event.
 * @param {Function} listener - The event listener callback.
 */
var addEventListener = exports.addEventListener = function addEventListener($element, type, listener) {
  if ($element.addEventListener) {
    $element.addEventListener(type, listener);
  } else {
    $element.attachEvent('on' + type, function () {
      listener.call($element);
    });
  }
};

/**
 * Reloads the current page.
 */
var reloadLocation = exports.reloadLocation = function reloadLocation() {
  window.location.reload(true);
};

/**
 * Redirects the user to the given URL.
 * @param {String} url - The URL.
 */
var setLocation = exports.setLocation = function setLocation(url) {
  window.location.href = url;
};

},{}]},{},[1]);
