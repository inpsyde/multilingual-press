import globalStub from '../../stubs/global';
import test from 'tape';
import sinon from 'sinon';
import * as F from '../../functions';
import JqueryObject from '../../stubs/JqueryObject';
import TermTranslator from '../../../../../resources/js/admin/term-translation/TermTranslator';

const { $ } = global;

test( 'handleTermInput ...', ( assert ) => {
	const testee = new TermTranslator();

	// Turn method into spy.
	testee.setTermOperation = sinon.spy();

	const event = {
		target: F.getRandomString()
	};

	const siteId = F.getRandomInteger();

	const $input = new JqueryObject();
	$input.data.withArgs( 'site' ).returns( siteId );

	$.withArgs( event.target ).returns( $input );

	testee.handleTermInput( event );

	assert.equal(
		testee.setTermOperation.calledWith( siteId, 'create' ),
		true,
		'... SHOULD set the create term operation for the expected site.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );

test( 'handleTermSelection ...', ( assert ) => {
	const testee = new TermTranslator();

	// Turn method into spy.
	testee.setTermOperation = sinon.spy();

	const event = {
		target: F.getRandomString()
	};

	const siteId = F.getRandomInteger();

	const $input = new JqueryObject();
	$input.data.withArgs( 'site' ).returns( siteId );

	$.withArgs( event.target ).returns( $input );

	testee.handleTermSelection( event );

	assert.equal(
		testee.setTermOperation.calledWith( siteId, 'select' ),
		true,
		'... SHOULD set the select term operation for the expected site.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );

test( 'setTermOperation ...', ( assert ) => {
	const testee = new TermTranslator();

	const siteId = F.getRandomInteger();

	const operation = F.getRandomString();

	const $radio = new JqueryObject();

	$.withArgs( `#mlp_related_term_op-${siteId}-${operation}` ).returns( $radio );

	testee.setTermOperation( siteId, operation );

	assert.equal(
		$radio.prop.calledWith( 'checked', true ),
		true,
		'... SHOULD select the expected radio input.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );
