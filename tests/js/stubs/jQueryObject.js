import sinon from "sinon";

const defaultMembers = {
	each: ( callback ) => callback(),
	find: sinon.stub(),
	text: sinon.stub(),
	val: sinon.stub()
};

const jQueryObject = function( customMembers = {} ) {
	const members = {};

	Object.keys( defaultMembers ).forEach( ( key ) => {
		members[ key ] = defaultMembers[ key ];
	} );

	Object.keys( customMembers ).forEach( ( key ) => {
		members[ key ] = customMembers[ key ];
	} );

	return members;
};

export default jQueryObject;
