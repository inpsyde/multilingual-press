import sinon from "sinon";

const defaultMembers = {
	text: sinon.stub(),
	val: sinon.stub()
};

export default function( customMembers = {} ) {
	const members = {};

	Object.keys( defaultMembers ).forEach( ( key ) => {
		members[ key ] = defaultMembers[ key ];
	} );

	Object.keys( customMembers ).forEach( ( key ) => {
		members[ key ] = customMembers[ key ];
	} );

	return members;
}
