import globalStub from "../../domStub";
import test from "tape";
// import sinon from "sinon";
// import * as F from "../../functions";
import CopyPost from "../../../../resources/js/admin/post-translation/CopyPost";
globalStub; // eslint...
// let { window } = globalStub;

const createTestee = () => {
	return new CopyPost();
};

test( 'CopyPost is a constructor function', ( assert ) => {

	assert.equal(
		typeof CopyPost,
		'function',
		'Registry SHOULD be a function.' );

	assert.equal(
		typeof createTestee(),
		'object',
		'Registry SHOULD construct an object.'
	);

	assert.end();
} );

