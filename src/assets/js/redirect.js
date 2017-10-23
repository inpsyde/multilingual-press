(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
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

},{}],2:[function(require,module,exports){
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

},{}],3:[function(require,module,exports){
'use strict';

var _functions = require('./common/functions');

var F = _interopRequireWildcard(_functions);

var _utils = require('./common/utils');

var Util = _interopRequireWildcard(_utils);

var _Redirector = require('./redirect/Redirector');

var _Redirector2 = _interopRequireDefault(_Redirector);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } else { var newObj = {}; if (obj != null) { for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) newObj[key] = obj[key]; } } newObj.default = obj; return newObj; } }

// Initialize the Redirector module.
new _Redirector2.default(F.getSettings('Redirector'), Util).initialize();

},{"./common/functions":1,"./common/utils":2,"./redirect/Redirector":4}],4:[function(require,module,exports){
'use strict';

exports.__esModule = true;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

// Internal pseudo-namespace for private data.
// NOTE: _this is shared between ALL instances of this module! So far, there is only one instance, so no problem NOW.
var _this = {};

/**
 * The MultilingualPress Redirector module.
 */

var Redirector = function () {
	/**
  * Constructor. Sets up the properties.
  * @param {Object} settings - The settings.
  * @param {Object} Util - The set of utility methods.
  */
	function Redirector(settings, Util) {
		_classCallCheck(this, Redirector);

		/**
   * The settings.
   * @type {Object}
   */
		_this.settings = settings;

		/**
   * The storage name.
   * @type {Object}
   */
		_this.storageName = 'mlpNoredirectStorage';

		/**
   * The storage timestamp name.
   * @type {Object}
   */
		_this.storageTimestampName = 'mlpNoredirectStorageTimestamp';

		/**
   * The preferred languages of the user.
   * @type {String[]}
   */
		_this.userLanguages = navigator.languages || [];

		/**
   * The set of utility methods.
   * @type {Object}
   */
		_this.Util = Util;
	}

	/**
  * Initializes the module.
  */


	Redirector.prototype.initialize = function initialize() {
		this.startTimestampUpdate();

		if (this.isCurrentLanguageStored()) {
			return;
		}

		var noredirect = this.getNoredirectLanguage();
		if (noredirect) {
			this.storeLanguage(noredirect);

			return;
		}

		var contentLanguage = this.findContentLanguage();
		if (contentLanguage) {
			this.redirect(contentLanguage);
		}
	};

	/**
  * Checks if the stored timestamp is valid.
  * @return {Boolean} Whether or not the stored timestamp is valid.
  */


	Redirector.prototype.checkTimestamp = function checkTimestamp() {
		var timestamp = Number(localStorage.getItem(_this.storageTimestampName));

		return Date.now() <= timestamp + Number(_this.settings.storageLifetime);
	};

	/**
  * Returns the best-matching content language, if any.
  * @return {String} The best-matching content language.
  */


	Redirector.prototype.findContentLanguage = function findContentLanguage() {
		if (_this.userLanguages.length && Object.keys(_this.settings.urls).length) {
			var contentLanguage = void 0;

			for (var _iterator = _this.userLanguages, _isArray = Array.isArray(_iterator), _i = 0, _iterator = _isArray ? _iterator : _iterator[Symbol.iterator]();;) {
				var _ref;

				if (_isArray) {
					if (_i >= _iterator.length) break;
					_ref = _iterator[_i++];
				} else {
					_i = _iterator.next();
					if (_i.done) break;
					_ref = _i.value;
				}

				var language = _ref;

				contentLanguage = this.matchLanguage(language);
				if (contentLanguage) {
					return contentLanguage;
				}
			}

			for (var _iterator2 = this.getAdditionalUserLanguages(), _isArray2 = Array.isArray(_iterator2), _i2 = 0, _iterator2 = _isArray2 ? _iterator2 : _iterator2[Symbol.iterator]();;) {
				var _ref2;

				if (_isArray2) {
					if (_i2 >= _iterator2.length) break;
					_ref2 = _iterator2[_i2++];
				} else {
					_i2 = _iterator2.next();
					if (_i2.done) break;
					_ref2 = _i2.value;
				}

				var _language = _ref2;

				contentLanguage = this.matchLanguage(_language);
				if (contentLanguage) {
					return contentLanguage;
				}
			}
		}

		return '';
	};

	/**
  * Returns the regionless languages of the user that have not been defined before.
  * @returns {String[]} The regionless languages of the user not defined before.
  */


	Redirector.prototype.getAdditionalUserLanguages = function getAdditionalUserLanguages() {
		var userLanguages = _this.userLanguages;

		return userLanguages.reduce(function (languages, language) {
			var index = language.indexOf('-');
			if (0 < index) {
				language = language.substr(0, index);

				if (!userLanguages.includes(language)) {
					languages.push(language);
				}
			}

			return languages;
		}, []);
	};

	/**
  * Returns the noredirect language included in the request, if any.
  * @returns {String} Language.
  */


	Redirector.prototype.getNoredirectLanguage = function getNoredirectLanguage() {
		var value = new RegExp('[\\?&]' + _this.settings.noredirectKey + '=([^?&#]*)').exec(window.location.href);

		return value ? decodeURIComponent(value[1].replace(/\+/g, ' ')) : '';
	};

	/**
  * Returns the stored languages.
  * @returns {String[]} The stored languages.
  */


	Redirector.prototype.getStoredLanguages = function getStoredLanguages() {
		if (!this.checkTimestamp()) {
			localStorage.removeItem(_this.storageName);

			return [];
		}

		var languages = localStorage.getItem(_this.storageName);

		return languages ? languages.split(' ') : [];
	};

	/**
  * Checks if the current site language is stored to not get redirected from.
  * @returns {Boolean} Whether or not the current site language is stored to not get redirected from.
  */


	Redirector.prototype.isCurrentLanguageStored = function isCurrentLanguageStored() {
		return this.getStoredLanguages().includes(this.normalizeLanguage(_this.settings.currentLanguage));
	};

	/**
  * Returns the best-matching content language for the given user language.
  * @param {String} userLanguage - A language of the user.
  * @return {String} The best-matching content language.
  */


	Redirector.prototype.matchLanguage = function matchLanguage(userLanguage) {
		if (_this.settings.urls[userLanguage]) {
			return userLanguage;
		}

		if (-1 === userLanguage.indexOf('-')) {
			var start = userLanguage + '-';

			for (var _iterator3 = Object.keys(_this.settings.urls), _isArray3 = Array.isArray(_iterator3), _i3 = 0, _iterator3 = _isArray3 ? _iterator3 : _iterator3[Symbol.iterator]();;) {
				var _ref3;

				if (_isArray3) {
					if (_i3 >= _iterator3.length) break;
					_ref3 = _iterator3[_i3++];
				} else {
					_i3 = _iterator3.next();
					if (_i3.done) break;
					_ref3 = _i3.value;
				}

				var contentLanguage = _ref3;

				if (contentLanguage.startsWith(start)) {
					return contentLanguage;
				}
			}
		}

		return '';
	};

	/**
  * Returns the given language in the noralized, locale-like form.
  * @param {String} language - The language.
  * @return {String} Normalized language.
  */


	Redirector.prototype.normalizeLanguage = function normalizeLanguage(language) {
		return language.replace(/-/, '_');
	};

	/**
  * Redirects to the URL according to the given language.
  * @param {String} language - A language.
  */


	Redirector.prototype.redirect = function redirect(language) {
		this.storeLanguage(language);

		if (language === _this.settings.currentLanguage) {
			return;
		}

		var url = _this.settings.urls[language].replace(/\?.*$/, '');

		_this.Util.setLocation(url + '?' + _this.settings.noredirectKey + '=' + this.normalizeLanguage(language));
	};

	/**
  * Starts the continuously running timestamp update used to determine the age of stored languages.
  */


	Redirector.prototype.startTimestampUpdate = function startTimestampUpdate() {
		var timeout = Number(_this.settings.updateTimestampInterval);
		if (0 < timeout) {
			var updateTimestamp = function updateTimestamp() {
				return localStorage.setItem(_this.storageTimestampName, Date.now());
			};
			updateTimestamp();
			setInterval(updateTimestamp, timeout);
		}
	};

	/**
  * Stores the given language.
  * @param {String} language - A language.
  */


	Redirector.prototype.storeLanguage = function storeLanguage(language) {
		language = this.normalizeLanguage(language);

		var languages = this.getStoredLanguages();
		if (languages.includes(language)) {
			return;
		}

		languages.push(language);

		localStorage.setItem(_this.storageName, languages.join(' '));
	};

	return Redirector;
}();

exports.default = Redirector;

},{}]},{},[3]);
