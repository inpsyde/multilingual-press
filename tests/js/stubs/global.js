import sinon from "sinon";
import Backbone from "./Backbone";
import jQueryStub from "./jQueryObject";

const jQuery = sinon.stub().returns( new jQueryStub() );

const window = {
	$: jQuery,
	_: sinon.stub(),
	Backbone,
	jQuery
};

const globalStub = {
	document: {},
	window
};

// Pollute the global scope.
Object.keys( globalStub ).forEach( ( key ) => {
		global[ key ] = globalStub[ key ];
	}
);

Object.keys( window ).forEach( ( key ) => {
		global[ key ] = globalStub[ key ] = window[ key ];
	}
);
