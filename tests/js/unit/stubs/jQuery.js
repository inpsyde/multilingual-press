import sinon from 'sinon';
import JqueryObject from './JqueryObject';

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
jQuery.each = ( o, c ) => {
	if ( Array.isArray( o ) ) {
		arrayEach( o, c );

		return;
	}

	objectEach( o, c );
};
jQuery.trim = ( a ) => a;

jQuery._restore = () => {
	jQuery.reset();

	// TODO: On each call, return a fresh JqueryObject. Depends on something like sinon.stub().returnsCallbackResult().
	jQuery.returns( new JqueryObject() );
};

jQuery._restore();

export default jQuery;
