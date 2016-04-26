import sinon from "sinon";

const defaultMembers = {
	text: sinon.stub(),
	val: sinon.stub()
};

export default function( members = {} ) {
	Object.keys( defaultMembers ).forEach( ( key ) => {
		this[ key ] = defaultMembers[ key ];
	} );

	Object.keys( members ).forEach( ( key ) => {
		this[ key ] = members[ key ];
	} );
};
