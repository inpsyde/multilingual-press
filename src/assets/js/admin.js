(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var _functions = require("./admin/core/functions");

var F = _interopRequireWildcard(_functions);

var _common = require("./admin/core/common");

var _Controller = require("./admin/core/Controller");

var _Controller2 = _interopRequireDefault(_Controller);

var _NavMenuItem = require("./admin/nav-menus/NavMenuItem");

var _NavMenuItem2 = _interopRequireDefault(_NavMenuItem);

var _NavMenus = require("./admin/nav-menus/NavMenus");

var _NavMenus2 = _interopRequireDefault(_NavMenus);

var _AddNewSite = require("./admin/network/AddNewSite");

var _AddNewSite2 = _interopRequireDefault(_AddNewSite);

var _TermTranslator = require("./admin/term-translation/TermTranslator");

var _TermTranslator2 = _interopRequireDefault(_TermTranslator);

var _UserBackEndLanguage = require("./admin/user-settings/UserBackEndLanguage");

var _UserBackEndLanguage2 = _interopRequireDefault(_UserBackEndLanguage);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } else { var newObj = {}; if (obj != null) { for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) newObj[key] = obj[key]; } } newObj.default = obj; return newObj; } }

var ajaxUrl = window.ajaxurl;

/**
 * The MultilingualPress admin namespace.
 * @namespace
 * @alias MultilingualPressAdmin
 */
var MLP = window.MultilingualPressAdmin = {};

var toggler = new _common.Toggler({
	el: 'body',
	events: {
		'click .mlp-click-toggler': 'toggleElement'
	}
});
/**
 * The MultilingualPress toggler instance.
 * @type {Toggler}
 */
MLP.toggler = toggler;

// Initialize the state togglers.
toggler.initializeStateTogglers();

var controller = new _Controller2.default();
/**
 * The MultilingualPress admin controller instance.
 * @type {Controller}
 */
MLP.controller = controller;

var settings = void 0;

// Register the NavMenus module for the Menus admin page.
settings = F.getSettings(_NavMenus2.default);
controller.registerModule('nav-menus.php', _NavMenus2.default, {
	el: '#' + settings.metaBoxID,
	events: {
		'click #submit-mlp-language': 'sendRequest'
	},
	model: new _NavMenuItem2.default({ urlRoot: ajaxUrl }),
	moduleSettings: settings
});

// Register the AddNewSite module for the Add New Site network admin page.
controller.registerModule('network/site-new.php', _AddNewSite2.default, {
	el: '#wpbody-content form',
	events: {
		'change #site-language': 'adaptLanguage',
		'change #mlp-base-site-id': 'togglePluginsRow'
	}
});

// Register the TermTranslator module for the Edit Tags admin page.
controller.registerModule('edit-tags.php', _TermTranslator2.default, {
	el: '#mlp-term-translations',
	events: {
		'change select': 'propagateSelectedTerm'
	}
});

// Register the UserBackEndLanguage module for the General Settings admin page.
controller.registerModule('options-general.php', _UserBackEndLanguage2.default, {
	el: '#WPLANG',
	moduleSettings: F.getSettings(_UserBackEndLanguage2.default)
}, function (module) {
	return module.updateSiteLanguage();
});

// Initialize the admin controller, and thus all modules registered for the current admin page.
jQuery(controller.initialize);

},{"./admin/core/Controller":2,"./admin/core/common":4,"./admin/core/functions":5,"./admin/nav-menus/NavMenuItem":6,"./admin/nav-menus/NavMenus":7,"./admin/network/AddNewSite":8,"./admin/term-translation/TermTranslator":9,"./admin/user-settings/UserBackEndLanguage":10}],2:[function(require,module,exports){
"use strict";

exports.__esModule = true;

var _functions = require("./functions");

var F = _interopRequireWildcard(_functions);

var _Router = require("./Router");

var _Router2 = _interopRequireDefault(_Router);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } else { var newObj = {}; if (obj != null) { for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) newObj[key] = obj[key]; } } newObj.default = obj; return newObj; } }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } } //
// TODO: Complete refactoring of the Controller class as well as its dependencies.
//

var $ = window.jQuery;
var _ = window._;

var mlpSettings = F.getSettings('mlpSettings');

var modules = {};

var registry = {};

var router = new _Router2.default();

/**
 * Registers the module with the given data for the given route.
 * @param {Object} moduleData - The module data.
 * @param {string} route - The route.
 */
var registerModuleForRoute = function registerModuleForRoute(moduleData, route) {
	registry[route] || (registry[route] = []);
	registry[route].push(moduleData);
};

/**
 * Sets up all routes with the according registered modules.
 */
var setUpRoutes = function setUpRoutes() {
	$.each(registry, function (route, routeModules) {
		router.route(route, route, function () {
			$.each(routeModules, function (index, moduleData) {
				var Constructor = moduleData.Constructor;
				var module = new Constructor(moduleData.options);
				modules[Constructor.name] = module;
				moduleData.callback && moduleData.callback(module);
			});
		});
	});
};

/**
 * Starts Backbone's history, unless it has been started already.
 * @returns {boolean}
 */
var maybeStartHistory = function maybeStartHistory() {
	if (Backbone.History.started) {
		return false;
	}

	Backbone.history.start({
		root: mlpSettings.urlRoot,
		pushState: true,
		hashChange: false
	});

	return true;
};

var Controller = function () {
	/**
  * Constructor. Sets up the properties.
  */

	function Controller() {
		_classCallCheck(this, Controller);

		/**
   * The MultilingualPress admin module instances.
   * @type {Object}
   */
		this.modules = modules;
	}

	/**
  * Registers a new module with the given Module callback under the given name for the given route.
  * @param {string|string[]} routes - The routes for the module.
  * @param {Function} Constructor - The constructor callback for the module.
  * @param {Object} [options={}] - Optional. The options for the module. Default to {}.
  * @param {Function} [callback=null] - Optional. The callback to execute after construction. Defaults to null.
  */


	Controller.prototype.registerModule = function registerModule(routes, Constructor) {
		var options = arguments.length <= 2 || arguments[2] === undefined ? {} : arguments[2];
		var callback = arguments.length <= 3 || arguments[3] === undefined ? null : arguments[3];

		var moduleData = {
			Constructor: Constructor,
			options: options,
			callback: callback
		};

		$.each(_.isArray(routes) ? routes : [routes], function (index, route) {
			registerModuleForRoute(moduleData, route);
		});
	};

	/**
  * Initializes the instance.
  */


	Controller.prototype.initialize = function initialize() {
		setUpRoutes();
		maybeStartHistory();
	};

	return Controller;
}();

exports.default = Controller;

},{"./Router":3,"./functions":5}],3:[function(require,module,exports){
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
		var options = arguments.length <= 0 || arguments[0] === undefined ? {} : arguments[0];

		_classCallCheck(this, Router);

		return _possibleConstructorReturn(this, _Backbone$Router.call(this, options));
	}

	return Router;
}(Backbone.Router);

exports.default = Router;

},{}],4:[function(require,module,exports){
'use strict';

exports.__esModule = true;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var $ = window.jQuery;

/**
 * MultilingualPress Toggler module.
 */

var Toggler = exports.Toggler = function (_Backbone$View) {
	_inherits(Toggler, _Backbone$View);

	/**
  * Constructor. Sets up the properties.
  * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
  */

	function Toggler() {
		var options = arguments.length <= 0 || arguments[0] === undefined ? {} : arguments[0];

		_classCallCheck(this, Toggler);

		return _possibleConstructorReturn(this, _Backbone$View.call(this, options));
	}

	/**
  * Initializes the togglers that work by using their individual state.
  */


	Toggler.prototype.initializeStateTogglers = function initializeStateTogglers() {
		var _this2 = this;

		$('.mlp-state-toggler').each(function (index, element) {
			var $toggler = $(element);
			$('[name="' + $toggler.attr('name') + '"]').on('change', {
				$toggler: $toggler
			}, _this2.toggleElementIfChecked);
		});
	};

	/**
  * Toggles the element with the ID given in the according data attribute.
  * @param {Event} event - The click event of a toggler element.
  * @returns {boolean} Whether or not the element has been toggled.
  */


	Toggler.prototype.toggleElement = function toggleElement(event) {
		var targetID = $(event.target).data('toggle-target');
		if (targetID) {
			$(targetID).toggle();

			return true;
		}

		return false;
	};

	/**
  * Toggles the element with the ID given in the according toggler's data attribute if the toggler is checked.
  * @param {Event} event - The change event of an input element.
  * @returns {boolean} Whether or not the element has been toggled.
  */


	Toggler.prototype.toggleElementIfChecked = function toggleElementIfChecked(event) {
		var $toggler = event.data.$toggler;

		var targetID = $toggler.data('toggle-target');
		if (targetID) {
			$(targetID).toggle($toggler.is(':checked'));

			return true;
		}

		return false;
	};

	return Toggler;
}(Backbone.View);

},{}],5:[function(require,module,exports){
'use strict';

exports.__esModule = true;

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol ? "symbol" : typeof obj; };

/**
 * Returns the name of the given thing.
 * @param {Function|string|object} module - The instance or constructor or name of a MulitilingualPress module.
 * @returns {string} The name of the module.
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
 * Returns the settings object for the given module or settings name.
 * @param {Function|string|object} module - The instance or constructor or name of a MulitilingualPress module.
 * @returns {Object} The settings object.
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

},{}],6:[function(require,module,exports){
"use strict";

exports.__esModule = true;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * The MultilingualPress nav menu item model.
 */

var NavMenuItem = function (_Backbone$Model) {
	_inherits(NavMenuItem, _Backbone$Model);

	/**
  * Constructor. Sets up the properties.
  * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
  */

	function NavMenuItem() {
		var options = arguments.length <= 0 || arguments[0] === undefined ? {} : arguments[0];

		_classCallCheck(this, NavMenuItem);

		var _this = _possibleConstructorReturn(this, _Backbone$Model.call(this, options));

		_this.urlRoot = options.urlRoot;
		return _this;
	}

	return NavMenuItem;
}(Backbone.Model);

exports.default = NavMenuItem;

},{}],7:[function(require,module,exports){
'use strict';

exports.__esModule = true;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var $ = window.jQuery;

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
		var options = arguments.length <= 0 || arguments[0] === undefined ? {} : arguments[0];

		_classCallCheck(this, NavMenus);

		/**
   * The jQuery object representing the MultilingualPress language checkboxes.
   * @type {jQuery}
   */

		var _this = _possibleConstructorReturn(this, _Backbone$View.call(this, options));

		_this.$languages = _this.$el.find('li [type="checkbox"]');

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
		_this.$spinner = _this.$el.find('.spinner');

		/**
   * The jQuery object representing the Languages meta box submit button.
   * @type {jQuery}
   */
		_this.$submit = _this.$el.find('#submit-mlp-language');

		_this.model = options.model;
		_this.listenTo(_this.model, 'change', _this.render);

		_this.moduleSettings = options.moduleSettings;
		return _this;
	}

	/**
  * Requests the according markup for the checked languages in the Languages meta box.
  * @param {Event} event - The click event of the submit button.
  */


	NavMenus.prototype.sendRequest = function sendRequest(event) {
		var data = {
			action: this.moduleSettings.action,
			menu: this.$menu.val(),
			mlp_sites: this.getSites()
		};
		data[this.moduleSettings.nonceName] = this.moduleSettings.nonce;

		event.preventDefault();

		this.$submit.prop('disabled', true);

		this.$spinner.addClass('is-active');

		this.model.fetch({
			data: data,
			processData: true
		});
	};

	/**
  * Returns the site IDs for the checked languages in the Languages meta box.
  * @returns {number[]} The site IDs.
  */


	NavMenus.prototype.getSites = function getSites() {
		var ids = [];

		this.$languages.filter(':checked').each(function (index, element) {
			ids.push(Number($(element).val() || 0));
		});

		return ids;
	};

	/**
  * Renders the nav menu item to the currently edited menu.
  */


	NavMenus.prototype.render = function render() {
		if (this.model.get('success')) {
			this.$menuToEdit.append(this.model.get('data'));
		}

		this.$languages.prop('checked', false);

		this.$spinner.removeClass('is-active');

		this.$submit.prop('disabled', false);
	};

	return NavMenus;
}(Backbone.View);

exports.default = NavMenus;

},{}],8:[function(require,module,exports){
'use strict';

exports.__esModule = true;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var $ = window.jQuery;

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
		var options = arguments.length <= 0 || arguments[0] === undefined ? {} : arguments[0];

		_classCallCheck(this, AddNewSite);

		var _this = _possibleConstructorReturn(this, _Backbone$View.call(this, options));

		_this.template = _.template($('#mlp-add-new-site-template').html() || '');

		/**
   * As of WordPress 4.5.0, there are now several action hooks on the Add New Site network admin page.
   * Due to our BC policy, we have to wait for WordPress 4.7.0 in order to make use of these, though.
   * TODO: Refactor this (and the according PHP parts) with the release of WordPress 4.7.0.
   */
		// FIRST render the template, THEN set up the properties using elements that just got injected into the DOM.
		_this.$el.find('.submit').before(_this.template());

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
		return _this;
	}

	/**
  * Sets MultilingualPress's language select to the currently selected site language.
  * @param {Event} event - The change event of the site language select element.
  * @returns {boolean} Whether or not the languages has been adapted.
  */


	AddNewSite.prototype.adaptLanguage = function adaptLanguage(event) {
		var language = this.getLanguage($(event.target));
		if (this.$language.find('[value="' + language + '"]').length) {
			this.$language.val(language);

			return true;
		}

		return false;
	};

	/**
  * Returns the selected language of the given select element.
  * @param {HTMLElement} $select - A select element.
  * @returns {string} The selected language.
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
		this.$pluginsRow.toggle(0 < $(event.target).val());
	};

	return AddNewSite;
}(Backbone.View);

exports.default = AddNewSite;

},{}],9:[function(require,module,exports){
'use strict';

exports.__esModule = true;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var $ = window.jQuery;

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
		var options = arguments.length <= 0 || arguments[0] === undefined ? {} : arguments[0];

		_classCallCheck(this, TermTranslator);

		/**
   * The jQuery object representing the MultilingualPress term selects.
   * @type {jQuery}
   */

		var _this = _possibleConstructorReturn(this, _Backbone$View.call(this, options));

		_this.$selects = _this.$el.find('select');

		/**
   * Flag to indicate an ongoing term propagation.
   * @type {boolean}
   */
		_this.isPropagating = false;
		return _this;
	}

	/**
  * Propagates the new value of one term select element to all other term select elements.
  * @param {Event} event - The change event of a term select element.
  */


	TermTranslator.prototype.propagateSelectedTerm = function propagateSelectedTerm(event) {
		var _this2 = this;

		var $select = void 0,
		    relation = void 0;

		if (this.isPropagating) {
			return;
		}

		this.isPropagating = true;

		$select = $(event.target);

		relation = this.getSelectedRelation($select);
		if ('' !== relation) {
			this.$selects.not($select).each(function (index, element) {
				_this2.selectTerm($(element), relation);
			});
		}

		this.isPropagating = false;
	};

	/**
  * Returns the relation of the given select element (i.e., its currently selected option).
  * @param {jQuery} $select - A select element.
  * @returns {string} The relation of the selected term.
  */


	TermTranslator.prototype.getSelectedRelation = function getSelectedRelation($select) {
		return $select.find('option:selected').data('relation') || '';
	};

	/**
  * Sets the given select element's value to that of the option with the given relation, or the first option.
  * @param {jQuery} $select - A select element.
  * @param {string} relation - The relation of a term.
  */


	TermTranslator.prototype.selectTerm = function selectTerm($select, relation) {
		var $option = $select.find('option[data-relation="' + relation + '"]');
		if ($option.length) {
			$select.val($option.val());
		} else if (this.getSelectedRelation($select)) {
			$select.val($select.find('option').first().val());
		}
	};

	return TermTranslator;
}(Backbone.View);

exports.default = TermTranslator;

},{}],10:[function(require,module,exports){
"use strict";

exports.__esModule = true;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

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
		var options = arguments.length <= 0 || arguments[0] === undefined ? {} : arguments[0];

		_classCallCheck(this, UserBackEndLanguage);

		var _this = _possibleConstructorReturn(this, _Backbone$View.call(this, options));

		_this.moduleSettings = options.moduleSettings;
		return _this;
	}

	/**
  * Sets the Site Language value to what it should be.
  */


	UserBackEndLanguage.prototype.updateSiteLanguage = function updateSiteLanguage() {
		this.$el.val(this.moduleSettings.locale);
	};

	return UserBackEndLanguage;
}(Backbone.View);

exports.default = UserBackEndLanguage;

},{}]},{},[1]);
