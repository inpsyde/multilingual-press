import test from "tape";
import sinon from "sinon";
import Util from "../../../resources/js/common/Util";

test( 'addEventListener attaches the given listener to the given element for the given event', ( assert ) => {
	assert.equal(
		typeof Util.addEventListener,
		'function',
		'addEventListener SHOULD be a function.'
	);

	const $element = {
		attachEvent: sinon.spy()
	};

	const type = 'type';

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
		'addEventListener SHOULD attach the expected event listener using IE8 methods for IE8 browsers.'
	);

	// Reset spy.
	$element.attachEvent.reset();

	// Add IE8+ method.
	$element.addEventListener = sinon.spy();

	Util.addEventListener( $element, type, listener );

	assert.equal(
		$element.attachEvent.callCount,
		0,
		'addEventListener SHOULD NOT attach an event listener using IE8 methods for IE8+ browsers.'
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

test( 'setLocation redirects the user to the given URL', ( assert ) => {
	assert.equal(
		typeof Util.setLocation,
		'function',
		'setLocation SHOULD be a function.'
	);

	global.window = {
		location: {
			href: ''
		}
	};

	const url = 'url';

	Util.setLocation( url );

	assert.equal(
		global.window.location.href,
		url,
		'setLocation SHOULD redirect the user to the given URL.'
	);

	assert.end();
} );
