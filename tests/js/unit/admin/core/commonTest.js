import globalStub from '../../stubs/global';
import test from 'tape';
import sinon from 'sinon';
import * as F from '../../functions';
import JqueryObject from '../../stubs/JqueryObject';
import { Toggler } from '../../../../../resources/js/admin/core/common';

const { $ } = global;

test( 'initializeStateToggler ...', ( assert ) => {
	const testee = new Toggler();

	const $toggler = new JqueryObject();
	$toggler.attr.returnsArg( 0 );

	const $togglers = new JqueryObject();

	$.withArgs( '[name="name"]' ).returns( $togglers );

	testee.initializeStateToggler( $toggler );

	assert.equal(
		$togglers.on.calledWith( 'change', { $toggler }, testee.toggleElementIfChecked ),
		true,
		'... SHOULD register the expected event handler.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );

test( 'initializeStateTogglers ...', ( assert ) => {
	const testee = new Toggler();

	// Turn method into spy.
	testee.initializeStateToggler = sinon.spy();

	const element = F.getRandomString();

	const _elements = F.getRandomArray( 1, 10, element );

	const $element = new JqueryObject();

	$
		.withArgs( '.mlp-state-toggler' ).returns( new JqueryObject( { _elements } ) )
		.withArgs( element ).returns( $element );

	testee.initializeStateTogglers();

	assert.equal(
		testee.initializeStateToggler.callCount,
		_elements.length,
		'... SHOULD initialize each state toggler.'
	);

	assert.equal(
		testee.initializeStateToggler.alwaysCalledWith( $element ),
		true,
		'... SHOULD initialize the expected state togglers.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );

test( 'toggleElement (invalid target) ...', ( assert ) => {
	const testee = new Toggler();

	const event = {
		target: F.getRandomString()
	};

	const $toggler = new JqueryObject();

	const $target = new JqueryObject();

	$
		.withArgs( event.target ).returns( $toggler )
		.returns( $target );

	testee.toggleElement( event );

	assert.equal(
		$target.toggle.callCount,
		0,
		'... SHOULD NOT toggle any elements.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );

test( 'toggleElement (valid target) ...', ( assert ) => {
	const testee = new Toggler();

	const event = {
		target: F.getRandomString()
	};

	const targetID = F.getRandomString();

	const $toggler = new JqueryObject();
	$toggler.data.withArgs( 'toggle-target' ).returns( targetID );

	const $target = new JqueryObject();

	$
		.withArgs( event.target ).returns( $toggler )
		.withArgs( targetID ).returns( $target );

	testee.toggleElement( event );

	assert.equal(
		$target.toggle.callCount,
		1,
		'... SHOULD toggle the expected element.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );

test( 'toggleElementIfChecked (unchecked) ...', ( assert ) => {
	const testee = new Toggler();

	const event = {
		data: {
			$toggler: new JqueryObject()
		}
	};

	const $target = new JqueryObject();

	$.returns( $target );

	testee.toggleElementIfChecked( event );

	assert.equal(
		$target.toggle.callCount,
		0,
		'... SHOULD NOT toggle any elements.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );

test( 'toggleElementIfChecked (checked) ...', ( assert ) => {
	const testee = new Toggler();

	const targetID = F.getRandomString();

	const isChecked = F.getRandomBool();

	const $toggler = new JqueryObject();
	$toggler.data.withArgs( 'toggle-target' ).returns( targetID );
	$toggler.is.withArgs( ':checked' ).returns( isChecked );

	const event = {
		data: {
			$toggler
		}
	};

	const $target = new JqueryObject();

	$.withArgs( targetID ).returns( $target );

	testee.toggleElementIfChecked( event );

	assert.equal(
		$target.toggle.calledWith( isChecked ),
		true,
		'... SHOULD toggle the expected element according to the toggler state.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );
