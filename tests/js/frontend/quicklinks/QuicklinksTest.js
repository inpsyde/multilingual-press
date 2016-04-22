import test from "tape";
import sinon from "sinon";
import * as F from "../../functions";
import Quicklinks from "../../../../resources/js/frontend/quicklinks/Quicklinks";

const Util = {};

const resetUtil = () => {
	Util.addEventListener = sinon.spy();
	Util.setLocation = sinon.spy();

	return Util;
};

const createTestee = ( selector ) => new Quicklinks( selector || 'selector', resetUtil() );

global.document = {};

const { document } = global;

test( 'Quicklinks is a constructor function', ( assert ) => {
	assert.equal(
		typeof Quicklinks,
		'function',
		'Quicklinks SHOULD be a function.'
	);

	assert.equal(
		typeof createTestee(),
		'object',
		'Quicklinks SHOULD construct an object.'
	);

	assert.end();
} );

test( 'selector behaves as expected', ( assert ) => {
	const selector = F.getRandomString();

	const testee = createTestee( selector );

	assert.equal(
		testee.selector,
		selector,
		'selector SHOULD return the expected value.'
	);

	assert.end();
} );

test( 'initialize behaves as expected', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		typeof testee.initialize,
		'function',
		'initialize SHOULD be a function.'
	);

	// Turn method into spy.
	testee.attachSubmitHandler = sinon.spy();

	testee.initialize();

	assert.equal(
		testee.attachSubmitHandler.callCount,
		1,
		'initialize SHOULD pass the expected callback to jQuery.'
	);

	assert.end();
} );

test( 'attachSubmitHandler behaves as expected for an incorrect selector', ( assert ) => {
	const testee = createTestee( 'incorrect-selector' );

	document.querySelector = F.returnNull;

	assert.equal(
		testee.attachSubmitHandler(),
		false,
		'attachSubmitHandler SHOULD return false for an incorrect selector.'
	);

	assert.end();
} );

test( 'attachSubmitHandler behaves as expected for the correct selector', ( assert ) => {
	const testee = createTestee( 'correct-selector' );

	const $element = F.getRandomString();

	document.querySelector = () => $element;

	assert.equal(
		testee.attachSubmitHandler(),
		true,
		'attachSubmitHandler SHOULD return true for the correct selector.'
	);

	assert.equal(
		Util.addEventListener.callCount,
		1,
		'attachSubmitHandler SHOULD attach one event handler for the correct selector.'
	);

	assert.equal(
		// The third argument (i.e., the listener) is missing because it is a bound function, which sinon cannot handle.
		Util.addEventListener.calledWith( $element, 'submit' ),
		true,
		'attachSubmitHandler SHOULD attach the expected event handler for the correct selector.'
	);

	assert.end();
} );

test( 'submitForm behaves as expected for a missing select element', ( assert ) => {
	const testee = createTestee();

	const event = {
		preventDefault: sinon.spy(),
		target: {
			querySelector: F.returnNull
		}
	};

	testee.submitForm( event );

	assert.equal(
		event.preventDefault.callCount,
		0,
		'submitForm SHOULD NOT call event.prevenDefault for a missing select element.'
	);

	assert.end();
} );

test( 'submitForm behaves as expected for a present select element', ( assert ) => {
	const testee = createTestee();

	const $select = {
		value: F.getRandomString()
	};

	const event = {
		preventDefault: sinon.spy(),
		target: {
			querySelector: () => $select
		}
	};

	testee.submitForm( event );

	assert.equal(
		event.preventDefault.callCount,
		1,
		'submitForm SHOULD call event.prevenDefault for a present select element.'
	);

	assert.equal(
		Util.setLocation.callCount,
		1,
		'submitForm should call Util.setLocation once for a present select element.'
	);

	assert.equal(
		Util.setLocation.calledWith( $select.value ),
		true,
		'submitForm SHOULD call Util.setLocation with the select value for a present select element.'
	);

	assert.end();
} );
