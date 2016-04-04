(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

// Externalize the jQuery alias.

var _MultilingualPress = require('./frontend/MultilingualPress');

var _MultilingualPress2 = _interopRequireDefault(_MultilingualPress);

var _Quicklinks = require('./frontend/quicklinks/Quicklinks');

var _Quicklinks2 = _interopRequireDefault(_Quicklinks);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

window.$ = window.jQuery;

/**
 * The MultilingualPress Quicklinks instance.
 * @type {Quicklinks}
 */
_MultilingualPress2.default.quicklinks = new _Quicklinks2.default('#mlp-quicklink-form');
_MultilingualPress2.default.quicklinks.initialize();

// Externalize the MultilingualPress namespace object.
window.MultilingualPress = _MultilingualPress2.default;

},{"./frontend/MultilingualPress":2,"./frontend/quicklinks/Quicklinks":3}],2:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
	value: true
});
/**
 * The MultilingualPress front end namespace object.
 * @namespace
 */
var MultilingualPress = {
	/**
  * Redirects the user to the given URL.
  * @param {string} url - The URL.
  */
	setLocation: function setLocation(url) {
		window.location.href = url;
	}
};

exports.default = MultilingualPress;

},{}],3:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

/**
 * The MultilingualPress Quicklinks module.
 */

var Quicklinks = function () {
	/**
  * Constructor. Sets up the properties.
  * @param {string} [selector] - The form element selector.
  */

	function Quicklinks(selector) {
		_classCallCheck(this, Quicklinks);

		/**
   * The form element selector.
   * @type {string}
   */
		this.selector = selector || '';
	}

	/**
  * Initializes the module.
  */


	_createClass(Quicklinks, [{
		key: 'initialize',
		value: function initialize() {
			this.attachSubmitHandler();
		}

		/**
   * Attaches the according handler to the form submit event.
   * @returns {boolean} - Whether or not the event handler has been attached.
   */

	}, {
		key: 'attachSubmitHandler',
		value: function attachSubmitHandler() {
			var $form = $(this.selector);
			if (!$form.length) {
				return false;
			}

			$form.on('submit', this.submitForm);

			return true;
		}

		/**
   * Triggers a redirect on form submission.
   * @param {Event} event - The submit event of the form.
   * @returns {boolean} - Whether or not redirect has been triggered.
   */

	}, {
		key: 'submitForm',
		value: function submitForm(event) {
			var $select = $(event.target).find('select');
			if (!$select.length) {
				return false;
			}

			event.preventDefault();

			window.MultilingualPress.setLocation($select.val());

			// For testing only.
			return true;
		}
	}]);

	return Quicklinks;
}();

exports.default = Quicklinks;

},{}]},{},[1]);
