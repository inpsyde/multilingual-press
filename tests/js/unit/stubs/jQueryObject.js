import * as _ from "lodash";
import sinon from "sinon";

export default function jQueryObject( customMembers = {} ) {
	const members = _.extend( {
		length: 1,

		find: sinon.stub(),
		text: sinon.stub(),
		val: sinon.stub()
	}, customMembers );
	Object.keys( members ).forEach( ( key ) => {
		// eslint-disable-next-line no-invalid-this
		this[ key ] = members[ key ];
	} );
}
