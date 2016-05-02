import sinon from "sinon";
import Backbone from "./Backbone";
import jQuery from "./jQuery";

const window = {
	$: jQuery,
	_: sinon.stub(),
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
