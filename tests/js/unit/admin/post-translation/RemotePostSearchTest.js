import "../../stubs/global";
import test from "tape";
import sinon from "sinon";
import * as _ from "lodash";
import * as F from "../../functions";
import Backbone from "../../stubs/Backbone";
import jQueryObject from "../../stubs/jQueryObject";
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

test( 'initializeResults ...', ( assert ) => {
	const testee = createTestee();

	// Turn method into spy.
	testee.initializeResult = sinon.spy();

	const element = F.getRandomString();

	const _elements = F.getRandomArray( 1, 10, element );

	$.withArgs( '.mlp-search-field' ).returns( new jQueryObject( { _elements } ) );

	testee.initializeResults();

	assert.equal(
		testee.initializeResult.callCount,
		_elements.length,
		'... SHOULD initialize each element.'
	);

	assert.equal(
		testee.initializeResult.alwaysCalledWith( element ),
		true,
		'... SHOULD initialize the expected elements.'
	);

	// Restore jQuery.
	$.reset();

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

test( 'reactToInput ...', ( assert ) => {
	const model = new Backbone.Model();

	const options = {
		model
	};

	const testee = createTestee( options );

	const target = 'target';

	const event = {
		target
	};

	const value = F.getRandomString();

	const $input = new jQueryObject();
	$input.data.withArgs( 'value' ).returns( value );
	$input.val.returns( value );

	$.withArgs( target ).returns( $input );

	testee.reactToInput( event );

	assert.equal(
		$input.data.callCount,
		1,
		'... SHOULD read input data.'
	);

	assert.equal(
		$input.data.calledWithExactly( 'value' ),
		true,
		'... SHOULD NOT write any input data in case of an unchanged input value.'
	);

	assert.equal(
		model.fetch.callCount,
		0,
		'... SHOULD NOT fetch new data in case of an unchanged input value.'
	);

	// Restore jQuery.
	$.reset();

	assert.end();
} );

test( 'reactToInput ...', ( assert ) => {
	const model = new Backbone.Model();

	const options = {
		model
	};

	const testee = createTestee( options );

	const target = 'target';

	const value = F.getRandomString();

	const $input = new jQueryObject();
	$input.data.withArgs( 'value' ).returns( 'value' );
	$input.val.returns( value );

	$.withArgs( target ).returns( $input );

	const event = {
		target
	};

	testee.reactToInput( event );

	assert.equal(
		$input.data.calledWithExactly( 'value' ),
		true,
		'... SHOULD read input data.'
	);

	assert.equal(
		$input.data.calledWithExactly( 'value', value ),
		true,
		'... SHOULD write input data in case of a changed input value.'
	);

	// Test a timed action after 1.5 times the original delay.
	setTimeout( () => {
		assert.equal(
			model.fetch.callCount,
			1,
			'... SHOULD fetch new data in case of a changed input value.'
		);
	}, 600 );

	// Restore jQuery.
	$.reset();

	assert.end();
} );

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
