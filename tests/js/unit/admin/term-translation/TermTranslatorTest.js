import globalStub from '../../stubs/global';
import test from 'tape';
import sinon from 'sinon';
import * as _ from 'lodash';
import * as F from '../../functions';
import JqueryObject from '../../stubs/JqueryObject';
import TermTranslator from '../../../../../resources/js/admin/term-translation/TermTranslator';

const { $ } = global;

/**
 * Returns a new instance of the class under test.
 * @param {Object} [options] - Optional. The constructor options.
 * @returns {TermTranslator} The instance of the class under test.
 */
const createTestee = ( options ) => {
	// Rewire internal data.
	TermTranslator.__Rewire__( '_this', {
		isPropagating: false
	} );

	return new TermTranslator( _.extend( { settings: {} }, options ) );
};

test( '$selects ...', ( assert ) => {
	const $selects = F.getRandomString();

	const options = {
		$el: new JqueryObject( {
			find: () => $selects
		} )
	};

	const testee = createTestee( options );

	assert.equal(
		testee.$selects,
		$selects,
		'... SHOULD have the expected value.'
	);

	assert.end();
} );

test( 'propagateSelectedTerm (propagating) ...', ( assert ) => {
	const testee = createTestee();

	// Rewire internal data.
	TermTranslator.__Rewire__( '_this', { isPropagating: true } );

	// Turn method into spy.
	testee.setTermOperation = sinon.spy();

	// Turn method into spy.
	testee.getSelectedRelationshipId = sinon.spy();

	// Turn method into spy.
	testee.selectTerm = sinon.spy();

	testee.propagateSelectedTerm( 'event' );

	assert.equal(
		testee.setTermOperation.callCount,
		0,
		'... SHOULD NOT call setTermOperation().'
	);

	assert.equal(
		testee.getSelectedRelationshipId.callCount,
		0,
		'... SHOULD NOT call getSelectedRelationshipId().'
	);

	assert.equal(
		testee.selectTerm.callCount,
		0,
		'... SHOULD NOT call selectTerm().'
	);

	assert.end();
} );

test( 'propagateSelectedTerm (not propagating, invalid relationship ID) ...', ( assert ) => {
	const testee = createTestee();

	// Turn method into spy.
	testee.setTermOperation = sinon.spy();

	// Turn method into stub.
	testee.getSelectedRelationshipId = sinon.stub().returns( 0 );

	// Turn method into spy.
	testee.selectTerm = sinon.spy();

	testee.propagateSelectedTerm( { target: 'target' } );

	assert.equal(
		testee.setTermOperation.callCount,
		1,
		'... SHOULD call setTermOperation().'
	);

	assert.equal(
		testee.getSelectedRelationshipId.callCount,
		1,
		'... SHOULD call getSelectedRelationshipId().'
	);

	assert.equal(
		testee.selectTerm.callCount,
		0,
		'... SHOULD NOT call selectTerm().'
	);

	assert.end();
} );

test( 'propagateSelectedTerm (not propagating, valid relationship ID) ...', ( assert ) => {
	const _elements = F.getRandomBoolArray();

	const $selects = new JqueryObject( { _elements } );
	$selects.not.returnsThis();

	const $el = new JqueryObject();
	$el.find.returns( $selects );

	const testee = createTestee( { $el } );

	// Turn method into spy.
	testee.setTermOperation = sinon.spy();

	// Turn method into stub.
	testee.getSelectedRelationshipId = sinon.stub().returns( F.getRandomInteger() );

	// Turn method into spy. The array elements are being used to set expectations on the setTermOperation() method.
	testee.selectTerm = sinon.stub();
	_elements.forEach( ( value, index ) => testee.selectTerm.onCall( index ).returns( value ) );

	testee.propagateSelectedTerm( { target: 'target' } );

	assert.equal(
		testee.getSelectedRelationshipId.callCount,
		1,
		'... SHOULD call getSelectedRelationshipId().'
	);

	assert.equal(
		testee.selectTerm.callCount,
		_elements.length,
		'... SHOULD call selectTerm() for each select element.'
	);

	assert.equal(
		testee.setTermOperation.callCount,
		_elements.filter( Boolean ).length + 1,
		'... SHOULD call setTermOperation() for every successful term selection (including the current term).'
	);

	assert.end();
} );

test( 'getSelectedRelationshipId (relationship ID missing) ...', ( assert ) => {
	const testee = createTestee();

	const $option = new JqueryObject();
	$option.data.returns( undefined );

	const $select = new JqueryObject();
	$select.find.returns( $option );

	assert.equal(
		testee.getSelectedRelationshipId( $select ),
		0,
		'... SHOULD return 0.'
	);

	assert.end();
} );

test( 'getSelectedRelationshipId (relationship ID specified) ...', ( assert ) => {
	const testee = createTestee();

	const relationshipId = F.getRandomInteger();

	const $option = new JqueryObject();
	$option.data.returns( relationshipId );

	const $select = new JqueryObject();
	$select.find.returns( $option );

	assert.equal(
		testee.getSelectedRelationshipId( $select ),
		relationshipId,
		'... SHOULD return the expected relationship ID.'
	);

	assert.end();
} );

test( 'selectTerm (matching relationship ID) ...', ( assert ) => {
	const testee = createTestee();

	const termID = F.getRandomInteger();

	const $option = new JqueryObject( {
		_elements: [ 'element' ]
	} );
	$option.val.returns( termID );

	const $select = new JqueryObject();
	$select.find.returns( $option );

	const relationshipId = F.getRandomInteger();

	assert.equal(
		testee.selectTerm( $select, relationshipId ),
		true,
		'... SHOULD return the expected result.'
	);

	assert.equal(
		$select.val.calledWith( termID ),
		true,
		'... SHOULD set the expected value.'
	);

	assert.end();
} );

test( 'selectTerm (no matching relationship ID) ...', ( assert ) => {
	const testee = createTestee();

	// Make method return a random integer (i.e., relation found).
	// Due to incompatible arguments, this has to stay an arrow function (i.e., not just a function reference).
	testee.getSelectedRelationshipId = () => F.getRandomInteger();

	const termID = F.getRandomInteger();

	const $option = new JqueryObject();
	$option.val.returns( termID );

	const $options = new JqueryObject();
	$options.first.returns( $option );

	const $select = new JqueryObject();
	$select.find.returns( $options );

	const relationshipId = F.getRandomInteger();

	assert.equal(
		testee.selectTerm( $select, relationshipId ),
		true,
		'... SHOULD return the expected result.'
	);

	assert.equal(
		$select.val.calledWith( termID ),
		true,
		'... SHOULD set the expected value.'
	);

	assert.end();
} );

test( 'selectTerm (relationship ID missing) ...', ( assert ) => {
	const testee = createTestee();

	// Make method return 0 (i.e., no relationship ID found).
	testee.getSelectedRelationshipId = () => 0;

	const $select = new JqueryObject();
	$select.find.returns( new JqueryObject() );

	const relationshipId = F.getRandomInteger();

	assert.equal(
		testee.selectTerm( $select, relationshipId ),
		false,
		'... SHOULD return the expected result.'
	);

	assert.equal(
		$select.val.callCount,
		0,
		'... SHOULD NOT set a term value.'
	);

	assert.end();
} );

test( 'setTermOperation ...', ( assert ) => {
	const testee = createTestee();

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

test( 'selectCreateTermOperation ...', ( assert ) => {
	const testee = createTestee();

	// Turn method into spy.
	testee.setTermOperation = sinon.spy();

	const event = {
		target: F.getRandomString()
	};

	const siteId = F.getRandomInteger();

	const $input = new JqueryObject();
	$input.data.withArgs( 'site' ).returns( siteId );

	$.withArgs( event.target ).returns( $input );

	testee.selectCreateTermOperation( event );

	assert.equal(
		testee.setTermOperation.calledWith( siteId, 'create' ),
		true,
		'... SHOULD set the create term operation for the expected site.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );
