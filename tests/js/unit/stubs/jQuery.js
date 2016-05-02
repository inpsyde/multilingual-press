import * as _ from "lodash";
import sinon from "sinon";
import jQueryObject from "./jQueryObject";

const arrayEach = ( a, c ) => {
	for ( let i = 0; i < a.length; i++ ) {
		c( i, a[ i ] );
	}
};

const objectEach = ( o, c ) => {
	for ( let k in o ) {
		if ( o.hasOwnProperty( k ) ) {
			c( k, o[ k ] );
		}
	}
};

// TODO: On each call, return a fresh jQueryObject. Depends on something like sinon.stub().returnsCallbackResult()`.
const jQuery = sinon.stub().returns( new jQueryObject() );
jQuery.ajax = sinon.stub();
jQuery.each = ( o = {}, c ) => _.isArray( o ) ? arrayEach( o, c ) : objectEach( o, c );
jQuery.trim = ( a ) => a;

export default jQuery;
