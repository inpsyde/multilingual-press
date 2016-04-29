import * as _ from "lodash";
import sinon from "sinon";
import jQueryObject from "./jQueryObject";

const arrayEach = ( a, c ) => a.forEach( c );

const objectEach = ( o, c ) => {
	for ( let k in o ) {
		if ( o.hasOwnProperty( k ) ) {
			c( k, o[ k ] );
		}
	}
};

const jQuery = sinon.stub().returns( new jQueryObject() );
jQuery.each = ( o = {}, c ) => _.isArray( o ) ? arrayEach( o, c ) : objectEach( o, c );

export default jQuery;
