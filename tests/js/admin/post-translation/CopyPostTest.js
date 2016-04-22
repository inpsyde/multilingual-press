import globalStub from "../../stubs/global";
import test from "tape";
import sinon from "sinon";
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
		'CopyPost SHOULD be a function.' );

	assert.equal(
		typeof createTestee(),
		'object',
		'CopyPost SHOULD construct an object.'
	);

	assert.end();
} );

test( 'CopyPost content getter should behave as expected', ( assert ) => {

	const testee = createTestee();
	assert.equal(
		testee.content,
		'',
		'Content SHOULD be empty string when jQuery selector is empty' );

	const testValue = 'LoremIpsum';

	window.$.val.returns( testValue );

	assert.equal(
		testee.content,
		testValue,
		'Content SHOULD equal test string when jQuery selector is not empty' );

	window.$.val.returns( undefined );

	assert.end();
} );

test( 'CopyPost excerpt getter should behave as expected', ( assert ) => {

	const testee = createTestee();
	assert.equal(
		testee.excerpt,
		'',
		'Excerpt SHOULD be empty string when jQuery selector is empty' );

	const testValue = 'LoremIpsum';

	window.$.val.returns( testValue );

	assert.equal(
		testee.excerpt,
		testValue,
		'Excerpt SHOULD equal test string when jQuery selector is not empty' );

	window.$.val.returns( undefined );

	assert.end();
} );

test( 'CopyPost slug getter should behave as expected', ( assert ) => {

	const testee = createTestee();
	assert.equal(
		testee.slug,
		'',
		'Slug SHOULD be empty string when jQuery selector is empty' );

	const testValue = 'LoremIpsum';

	window.$.text.returns( testValue );

	assert.equal(
		testee.slug,
		testValue,
		'Slug SHOULD equal test string when jQuery selector is not empty' );

	window.$.text.returns( undefined );

	assert.end();
} );

test( 'CopyPost title getter should behave as expected', ( assert ) => {

	const testee = createTestee();
	assert.equal(
		testee.title,
		'',
		'Title SHOULD be empty string when jQuery selector is empty' );

	const testValue = 'LoremIpsum';

	window.$.val.returns( testValue );

	assert.equal(
		testee.title,
		testValue,
		'Title SHOULD equal test string when jQuery selector is not empty' );

	window.$.val.returns( undefined );

	assert.end();
} );


//TODO: Test for copyPostData


test( 'getRemoteSiteID behaves as expected', ( assert ) => {

	const testee = createTestee();

	const testButton = {
		data: sinon.stub()
	};
	testButton.data.returns( 42 );

	assert.equal(
		testee.getRemoteSiteID( testButton ),
		42,
		'Return value should be the same as the test object data' );

	testButton.data.returns( "42.0" );
	assert.equal(
		testee.getRemoteSiteID( testButton ),
		42.0,
		'Return value should be numeric' );

	assert.end();
} );

