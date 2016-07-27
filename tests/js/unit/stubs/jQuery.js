import sinon from "sinon";
import jQueryObject from "./jQueryObject";

const arrayEach = ( a, c ) => {
	a.forEach( ( e, i ) => {
		c( i, e );
	} );
};

const objectEach = ( o, c ) => {
	arrayEach( Object.keys( o ), c );
};

const jQuery = sinon.stub();
jQuery.ajax = sinon.stub();
jQuery.each = ( o = {}, c ) => Array.isArray( o ) ? arrayEach( o, c ) : objectEach( o, c );
jQuery.trim = ( a ) => a;

jQuery._restore = () => {
	jQuery.reset().resetBehavior();

	// TODO: On each call, return a fresh jQueryObject. Depends on something like sinon.stub().returnsCallbackResult()`.
	jQuery.returns( new jQueryObject() );
};

jQuery._restore();

export default jQuery;
