import test from "tape";
import sinon from "sinon";
import * as F from "../../functions";
import Quicklinks from "../../../../../resources/js/frontend/quicklinks/Quicklinks";

const document = global.document = {};

const Util = {};

/**
 * Returns a fresh Util stub.
 * @returns {Object} The Util stub.
 */
const resetUtil = () => {
	Util.addEventListener = sinon.spy();
	Util.setLocation = sinon.spy();

	return Util;
};

/**
 * Returns a new instance of the class under test.
 * @param {string} [selector] - Optional. The form element selector. Defaults to 'selector'.
 * @returns {Quicklinks} The instance of the class under test.
 */
const createTestee = ( selector ) => new Quicklinks( selector || 'selector', resetUtil() );

test( 'selector ...', ( assert ) => {
	const selector = F.getRandomString();

	const testee = createTestee( selector );

	assert.equal(
		testee.selector,
		selector,
		'... SHOULD have the expected value.'
	);

	assert.end();
} );

test( 'initialize ...', ( assert ) => {
	const testee = createTestee();

	// Turn method into spy.
	testee.attachSubmitHandler = sinon.spy();

	testee.initialize();

	assert.equal(
		testee.attachSubmitHandler.callCount,
		1,
		'... SHOULD call attachSubmitHandler().'
	);

	assert.end();
} );

test( 'attachSubmitHandler (incorrect selector) ...', ( assert ) => {
	const testee = createTestee( 'incorrect-selector' );

	document.querySelector = F.returnNull;

	assert.equal(
		testee.attachSubmitHandler(),
		false,
		'... SHOULD return false.'
	);

	assert.equal(
		Util.addEventListener.callCount,
		0,
		'... SHOULD NOT attach any event handlers.'
	);

	// Restore global scope.
	delete document.querySelector;

	assert.end();
} );

test( 'attachSubmitHandler (correct selector) ...', ( assert ) => {
	const testee = createTestee( 'correct-selector' );

	const $element = F.getRandomString();

	document.querySelector = () => $element;

	assert.equal(
		testee.attachSubmitHandler(),
		true,
		'... SHOULD return true.'
	);

	assert.equal(
		Util.addEventListener.callCount,
		1,
		'... SHOULD attach one event handler.'
	);

	assert.equal(
		// The third argument (i.e., the listener) is missing as it is a bound function, which sinon cannot handle.
		Util.addEventListener.calledWith( $element, 'submit' ),
		true,
		'... SHOULD attach the expected event handler.'
	);

	// Restore global scope.
	delete document.querySelector;

	assert.end();
} );

test( 'submitForm (element missing) ...', ( assert ) => {
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
		'... SHOULD NOT call event.prevenDefault.'
	);

	assert.end();
} );

test( 'submitForm (element present) ...', ( assert ) => {
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
		'... SHOULD call event.prevenDefault.'
	);

	assert.equal(
		Util.setLocation.callCount,
		1,
		'... should call Util.setLocation once.'
	);

	assert.equal(
		Util.setLocation.calledWith( $select.value ),
		true,
		'... SHOULD call Util.setLocation with the select value.'
	);

	assert.end();
} );
