import test from "tape";
import sinon from "sinon";
import Quicklinks from "../../../../resources/js/frontend/quicklinks/Quicklinks";

global.$ = sinon.spy();

global.window = {
	MultilingualPress: {
		setLocation: sinon.spy()
	}
};

function createTestee( $, selector ) {
	global.$ = $ || sinon.spy();

	global.window.MultilingualPress.setLocation.reset();

	return new Quicklinks( selector );
}

test( 'Quicklinks is a constructor function', function( assert ) {
	assert.equal(
		typeof Quicklinks,
		'function',
		'Quicklinks should be a function.'
	);

	assert.equal(
		typeof new Quicklinks(),
		'object',
		'Quicklinks should construct an object.'
	);

	assert.end();
} );

test( 'initialize behaves as expected', function( assert ) {
	const testee = createTestee( sinon.spy( function( callback ) {
		callback();
	} ) );

	assert.equal(
		typeof testee.initialize,
		'function',
		'initialize should be a function.'
	);

	// Turn method into spy.
	testee.attachSubmitHandler = sinon.spy();

	testee.initialize();

	assert.equal(
		testee.attachSubmitHandler.callCount,
		1,
		'initialize should pass the expected callback to jQuery.'
	);

	assert.end();
} );

test( 'attachSubmitHandler behaves as expected for an incorrect selector', function( assert ) {
	const testee = createTestee( sinon.spy( function() {
		return [];
	} ), 'incorrect-selector' );

	assert.equal(
		testee.attachSubmitHandler(),
		false,
		'attachSubmitHandler should return false for an incorrect selector.'
	);

	assert.end();
} );

test( 'attachSubmitHandler behaves as expected for the correct selector', function( assert ) {
	const on = sinon.spy();

	const testee = createTestee( sinon.spy( function() {
		return {
			length: 1,
			on: on
		};
	} ), 'correct-selector' );

	assert.equal(
		testee.attachSubmitHandler(),
		true,
		'attachSubmitHandler should return true for the correct selector.'
	);

	assert.equal(
		on.callCount,
		1,
		'attachSubmitHandler should attach one event handler for the correct selector.'
	);

	assert.equal(
		on.calledWith( 'submit', testee.submitForm ),
		true,
		'attachSubmitHandler should attach the expected event handler for the correct selector.'
	);

	assert.end();
} );

test( 'submitForm behaves as expected for an incorrect target', function( assert ) {
	const testee = createTestee( sinon.spy( function() {
		return {
			find: function() {
				return [];
			}
		};
	} ) );

	assert.equal(
		testee.submitForm( { target: 'incorrect' } ),
		false,
		'submitForm should return false for an incorrect target.'
	);

	assert.end();
} );

test( 'submitForm behaves as expected for the correct target', function( assert ) {
	const selectValue = 'value';

	const testee = createTestee( sinon.spy( function() {
		return {
			find: function() {
				return {
					length: 1,
					val: function() {
						return selectValue;
					}
				};
			}
		};
	} ) );

	const preventDefault = sinon.spy();
	const event = {
		target: 'correct',
		preventDefault: preventDefault
	};

	assert.equal(
		testee.submitForm( event ),
		true,
		'submitForm should return true for the correct target.'
	);

	assert.equal(
		preventDefault.callCount,
		1,
		'submitForm should call event.prevenDefault for the correct target.'
	);

	assert.equal(
		global.window.MultilingualPress.setLocation.callCount,
		1,
		'submitForm should call window.MultilingualPress.setLocation once for the correct target.'
	);

	assert.equal(
		global.window.MultilingualPress.setLocation.calledWith( selectValue ),
		true,
		'submitForm should call window.MultilingualPress.setLocation with the select value for the correct target.'
	);

	assert.end();
} );
