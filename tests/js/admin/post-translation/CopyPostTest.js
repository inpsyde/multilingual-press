import "../../stubs/global";
import test from "tape";
// import sinon from "sinon";
import * as F from "../../functions";
import CopyPost from "../../../../resources/js/admin/post-translation/CopyPost";

const { $ } = global;

const createTestee = () => {
	return new CopyPost();
};

test( 'CopyPost is a constructor function', ( assert ) => {
	assert.equal(
		typeof CopyPost,
		'function',
		'CopyPost SHOULD be a function.'
	);

	assert.equal(
		typeof createTestee(),
		'object',
		'CopyPost SHOULD construct an object.'
	);

	assert.end();
} );

test( 'content behaves as expected if the element does not exist', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.content,
		'',
		'content SHOULD be empty if the element does not exist.'
	);

	assert.end();
} );

test( 'content behaves as expected if the element does exist', ( assert ) => {
	const content = F.getRandomString();

	$.withArgs( '#content' ).returns( {
		val: () => content
	} );

	const testee = createTestee();

	assert.equal(
		testee.content,
		content,
		'content SHOULD have the value of the according element.'
	);

	assert.end();
} );

test( 'excerpt behaves as expected if the element does not exist', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.excerpt,
		'',
		'excerpt SHOULD be empty if the element does not exist.'
	);

	assert.end();
} );

test( 'excerpt behaves as expected if the element does exist', ( assert ) => {
	const excerpt = F.getRandomString();

	$.withArgs( '#excerpt' ).returns( {
		val: () => excerpt
	} );

	const testee = createTestee();

	assert.equal(
		testee.excerpt,
		excerpt,
		'excerpt SHOULD have the value of the according element.'
	);

	assert.end();
} );

test( 'slug behaves as expected if the element does not exist', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.slug,
		'',
		'slug SHOULD be empty if the element does not exist.'
	);

	assert.end();
} );

test( 'slug behaves as expected if the element does exist', ( assert ) => {
	const slug = F.getRandomString();

	$.withArgs( '#editable-post-name-full' ).returns( {
		text: () => slug
	} );

	const testee = createTestee();

	assert.equal(
		testee.slug,
		slug,
		'excerpt SHOULD have the value of the according element.'
	);

	assert.end();
} );

test( 'title behaves as expected if the element does not exist', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.title,
		'',
		'title SHOULD be empty if the element does not exist.'
	);

	assert.end();
} );

test( 'title behaves as expected if the element does exist', ( assert ) => {
	const title = F.getRandomString();

	$.withArgs( '#title' ).returns( {
		val: () => title
	} );

	const testee = createTestee();

	assert.equal(
		testee.title,
		title,
		'title SHOULD have the value of the according element.'
	);

	assert.end();
} );

// TODO: Test copyPostData

test( 'getRemoteSiteID behaves as expected', ( assert ) => {
	const testee = new CopyPost();

	const siteID = F.getRandomInteger();

	const $button = {
		data: () => siteID
	};

	assert.equal(
		testee.getRemoteSiteID( $button ),
		siteID,
		'getRemoteSiteID SHOULD return the expected value.'
	);

	assert.end();
} );

// TODO: Test missing methods...
