import sinon from "sinon";
import jQueryStub from "./jQueryObject";

const _ = sinon.stub();

const Backbone = {
	Events: {},
	history: {
		start: sinon.spy()
	},
	History: {
		started: false
	},
	Model: () => {},
	Router: () => {},
	View: () => {}
};
Backbone.Model.prototype.fetch = sinon.spy();
Backbone.Model.prototype.get = sinon.stub();
Backbone.Router.prototype.route = sinon.spy();
Backbone.View.prototype.listenTo = sinon.spy();

const document = {};

const jQuery = sinon.stub().returns( new jQueryStub() );

const window = {
	$: jQuery,
	_,
	Backbone,
	jQuery
};

const globalStub = {
	document,
	window
};

// Pollute the global scope.
Object.keys( globalStub ).forEach( ( key ) => {
	global[ key ] = globalStub[ key ];
} );

Object.keys( window ).forEach( ( key ) => {
	global[ key ] = globalStub[ key ] = window[ key ];
} );
