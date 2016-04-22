import globalStub from "../../stubs/global";
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

test( 'CopyPost content getter should behave as expected', ( assert ) => {

	const testee = createTestee();
	assert.equal(
		testee.content,
		'',
		'Content should be empty string when jQuery selector is empty' );

	const testValue = 'LoremIpsum';

	window.$.val.returns( testValue );

	assert.equal(
		testee.content,
		testValue,
		'Content should equal test string when jQuery selector is not empty' );

	window.$.val.reset();

	assert.end();
} );

