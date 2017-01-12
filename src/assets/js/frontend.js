(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
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

},{}],2:[function(require,module,exports){
'use strict';

var _utils = require('./common/utils');

var Util = _interopRequireWildcard(_utils);

var _Quicklinks = require('./frontend/quicklinks/Quicklinks');

var _Quicklinks2 = _interopRequireDefault(_Quicklinks);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } else { var newObj = {}; if (obj != null) { for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) newObj[key] = obj[key]; } } newObj.default = obj; return newObj; } }

/**
 * The MultilingualPress front end namespace.
 * @namespace
 * @alias MultilingualPress
 */
var MLP = {
	/**
  * The MultilingualPress Quicklinks instance.
  * @type {Quicklinks}
  */
	quicklinks: new _Quicklinks2.default('.mlp-quicklink-form', Util),

	/**
  * The set of utility methods.
  * @type {Object}
  */
	Util: Util
};

var quicklinks = MLP.quicklinks;

// Initialize the Quicklinks module.

quicklinks.initialize();

// Externalize the MultilingualPress front end namespace.
window.MultilingualPress = MLP;

},{"./common/utils":1,"./frontend/quicklinks/Quicklinks":3}],3:[function(require,module,exports){
'use strict';

exports.__esModule = true;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

// Internal pseudo-namespace for private data.
// NOTE: _this is shared between ALL instances of this module! So far, there is only one instance, so no problem NOW.
var _this = {};

/**
 * The MultilingualPress Quicklinks module.
 */

var Quicklinks = function () {
	/**
  * Constructor. Sets up the properties.
  * @param {String} selector - The form element selector.
  * @param {Object} Util - The set of utility methods.
  */
	function Quicklinks(selector, Util) {
		_classCallCheck(this, Quicklinks);

		/**
   * The form element selector.
   * @type {String}
   */
		_this.selector = selector;

		/**
   * The set of utility methods.
   * @type {Object}
   */
		_this.Util = Util;
	}

	/**
  * Returns the form element selector.
  * @returns {String} The form element selector.
  */


	/**
  * Initializes the module.
  */
	Quicklinks.prototype.initialize = function initialize() {
		this.attachSubmitHandler();
	};

	/**
  * Attaches the according handler to the form submit event.
  * @returns {Boolean} Whether or not the event handler has been attached.
  */


	Quicklinks.prototype.attachSubmitHandler = function attachSubmitHandler() {
		var $form = document.querySelector(this.selector);

		if (!$form) {
			return false;
		}

		_this.Util.addEventListener($form, 'submit', this.submitForm.bind(this));

		return true;
	};

	/**
  * Triggers a redirect on form submission.
  * @param {Event} event - The submit event of the form.
  */


	Quicklinks.prototype.submitForm = function submitForm(event) {
		var $select = event.target.querySelector('select');

		if (!$select) {
			return;
		}

		event.preventDefault();

		_this.Util.setLocation($select.value);
	};

	_createClass(Quicklinks, [{
		key: 'selector',
		get: function get() {
			return _this.selector;
		}
	}]);

	return Quicklinks;
}();

exports.default = Quicklinks;

},{}]},{},[2]);
