import test from "tape";
import sinon from "sinon";
import * as F from "../functions";
import * as Util from "../../../resources/js/common/utils";

test( 'addEventListener attaches the expected event listener using IE8 methods for IE8 browsers', ( assert ) => {
	const $element = {
		attachEvent: sinon.spy()
	};

	const type = F.getRandomString();

	const listener = sinon.spy();

	Util.addEventListener( $element, type, listener );

	assert.equal(
		$element.attachEvent.callCount,
		1,
		'addEventListener SHOULD attach one event listener using IE8 methods for IE8 browsers.'
	);

	assert.equal(
		$element.attachEvent.calledWith( 'on' + type ),
		true,
		'addEventListener SHOULD attach the event listener on the expected event using IE8 methods for IE8 browsers.'
	);

	// Execute the callback passed as second argument.
	$element.attachEvent.firstCall.args[ 1 ]();

	assert.equal(
		listener.callCount,
		1,
		'addEventListener SHOULD attach the expected event listener using IE8 methods for IE8 browsers.'
	);

	assert.equal(
		listener.calledOn( $element ),
		true,
		'addEventListener SHOULD specify the expected context for the event listener using IE8 methods for IE8 browsers.'
	);

	assert.end();
} );

test( 'addEventListener attaches the expected event listener using IE8+ methods for IE8+ browsers', ( assert ) => {
	const $element = {
		addEventListener: sinon.spy(),
		attachEvent: sinon.spy()
	};

	const type = F.getRandomString();

	const listener = sinon.spy();

	Util.addEventListener( $element, type, listener );

	assert.equal(
		$element.attachEvent.callCount,
		0,
		'addEventListener SHOULD NOT attach any event listeners using IE8 methods for IE8+ browsers.'
	);

	assert.equal(
		$element.addEventListener.callCount,
		1,
		'addEventListener SHOULD attach one event listener using IE8+ methods for IE8+ browsers.'
	);

	assert.equal(
		$element.addEventListener.calledWith( type, listener ),
		true,
		'addEventListener SHOULD attach the expected event listener using IE8+ methods for IE8+ browsers.'
	);

	assert.end();
} );

test( 'reloadLocation reloads the current page', ( assert ) => {
	global.window = {
		location: {
			reload: sinon.spy()
		}
	};

	Util.reloadLocation();

	assert.equal(
		global.window.location.reload.callCount,
		1,
		'reloadLocation SHOULD reload the current page.'
	);

	assert.equal(
		global.window.location.reload.calledWith( true ),
		true,
		'reloadLocation SHOULD reload the current page via a GET request.'
	);

	assert.end();
} );

test( 'setLocation redirects the user to the given URL', ( assert ) => {
	global.window = {
		location: {
			href: ''
		}
	};

	const url = F.getRandomString();

	Util.setLocation( url );

	assert.equal(
		global.window.location.href,
		url,
		'setLocation SHOULD redirect the user to the given URL.'
	);

	assert.end();
} );
