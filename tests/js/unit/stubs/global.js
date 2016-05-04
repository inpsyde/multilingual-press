import sinon from "sinon";
import _ from "./underscore";
import Backbone from "./Backbone";
import jQuery from "./jQuery";

const window = {
	$: jQuery,
	_,
	ajaxurl: 'ajaxurl',
	alert: sinon.spy(),
	Backbone,
	confirm: sinon.stub(),
	jQuery
};

const globalStub = {
	document: {},
	window
};

// Pollute the global scope.
Object.keys( globalStub ).forEach( ( key ) => {
	global[ key ] = globalStub[ key ];
} );

Object.keys( window ).forEach( ( key ) => {
	global[ key ] = globalStub[ key ] = window[ key ];
} );

globalStub.restore = () => {
	_._restore();
	jQuery._restore();
};

export default globalStub;
