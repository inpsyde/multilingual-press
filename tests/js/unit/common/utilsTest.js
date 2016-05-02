import test from "tape";
import sinon from "sinon";
import * as F from "../functions";
import * as Util from "../../../../resources/js/common/utils";

const window = global.window = {
	location: {
		href: '',
		reload: sinon.spy()
	}
};

test( 'addEventListener ...', ( assert ) => {
	const $element = {
		attachEvent: sinon.spy()
	};

	const type = F.getRandomString();

	const listener = sinon.spy();

	Util.addEventListener( $element, type, listener );

	assert.equal(
		$element.attachEvent.callCount,
		1,
		'... SHOULD attach one event listener using IE8 methods for IE8 browsers.'
	);

	assert.equal(
		$element.attachEvent.calledWith( 'on' + type ),
		true,
		'... SHOULD attach the event listener on the expected event using IE8 methods for IE8 browsers.'
	);

	// Execute the callback passed as second argument.
	$element.attachEvent.firstCall.args[ 1 ]();

	assert.equal(
		listener.callCount,
		1,
		'... SHOULD attach the expected event listener using IE8 methods for IE8 browsers.'
	);

	assert.equal(
		listener.calledOn( $element ),
		true,
		'... SHOULD specify the expected context for the event listener using IE8 methods for IE8 browsers.'
	);

	assert.end();
} );

test( 'addEventListener ...', ( assert ) => {
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
		'... SHOULD NOT attach any event listeners using IE8 methods for IE8+ browsers.'
	);

	assert.equal(
		$element.addEventListener.callCount,
		1,
		'... SHOULD attach one event listener using IE8+ methods for IE8+ browsers.'
	);

	assert.equal(
		$element.addEventListener.calledWith( type, listener ),
		true,
		'... SHOULD attach the expected event listener using IE8+ methods for IE8+ browsers.'
	);

	assert.end();
} );

test( 'reloadLocation ...', ( assert ) => {
	window.location.reload.reset();

	Util.reloadLocation();

	assert.equal(
		window.location.reload.callCount,
		1,
		'... SHOULD reload the current page.'
	);

	assert.equal(
		window.location.reload.calledWith( true ),
		true,
		'... SHOULD reload the current page via a GET request.'
	);

	assert.end();
} );

test( 'setLocation ...', ( assert ) => {
	window.location.href = '';

	const url = F.getRandomString();

	Util.setLocation( url );

	assert.equal(
		window.location.href,
		url,
		'... SHOULD redirect the user to the given URL.'
	);

	assert.end();
} );
