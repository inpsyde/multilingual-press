import "../../stubs/global";
import test from "tape";
import sinon from "sinon";
import * as _ from "lodash";
import * as F from "../../functions";
import Backbone from "../../stubs/Backbone";
import RemotePostSearch from "../../../../../resources/js/admin/post-translation/RemotePostSearch";

const { $ } = global;

/**
 * Returns a new instance of the class under test.
 * @param {Object} [options] - Optional. The constructor options.
 * @returns {RemotePostSearch} The instance of the class under test.
 */
const createTestee = ( options ) => new RemotePostSearch( _.extend( { settings: {} }, options ) );

test( 'constructor ...', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.listenTo.callCount,
		1,
		'... SHOULD attach an event listener.'
	);

	assert.equal(
		testee.listenTo.calledWith( testee.model, 'change', testee.render ),
		true,
		'... SHOULD attach the expected event listener.'
	);

	assert.end();
} );

test( 'settings ...', ( assert ) => {
	const options = {
		settings: F.getRandomString()
	};

	const testee = createTestee( options );

	assert.equal(
		testee.settings,
		options.settings,
		'... SHOULD have the expected value.'
	);

	assert.end();
} );

test( 'initializeResult ...', ( assert ) => {
	assert.pass( '... works on internals only, hence it is no subject to unit testing.' );

	assert.end();
} );

// TODO: Unskip as soon as $.each() has been committed.
test.skip( 'initializeResults ...', ( assert ) => {
	const testee = createTestee();

	// Turn method into spy.
	testee.initializeResult = sinon.spy();

	const element = F.getRandomString();

	const $elements = F.getRandomArray( 10, element );

	$.withArgs( '.mlp-search-field' ).returns( $elements );

	testee.initializeResults();

	assert.equal(
		testee.initializeResult.callCount,
		$elements.length,
		'... SHOULD initialize each element.'
	);

	assert.equal(
		testee.initializeResult.alwaysCalledWith( element ),
		true,
		'... SHOULD initialize the expected elements.'
	);

	assert.end();
} );

test( 'preventFormSubmission ...', ( assert ) => {
	const testee = createTestee();

	const event = {
		preventDefault: sinon.spy()
	};

	// Maybe indicate Enter key...
	if ( F.getRandomBool() ) {
		event.which = 13;
	}

	testee.preventFormSubmission( event );

	assert.equal(
		event.preventDefault.callCount,
		13 === event.which ? 1 : 0,
		'... SHOULD prevent form submission IF the Enter key was pressed.'
	);

	assert.end();
} );

// TODO: Test reactToInput().

test( 'render ...', ( assert ) => {
	const success = F.getRandomBool();

	const model = new Backbone.Model();
	model.get
		.withArgs( 'success' ).returns( success )
		.withArgs( 'data' ).returns( { remoteSiteID: F.getRandomInteger() } );

	const options = {
		model
	};

	const testee = createTestee( options );

	assert.equal(
		testee.render(),
		success,
		'... SHOULD return expected result.'
	);

	assert.end();
} );
