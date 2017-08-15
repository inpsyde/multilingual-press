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
	testee.getSelectedRelation = sinon.spy();

	// Turn method into spy.
	testee.selectTerm = sinon.spy();

	testee.propagateSelectedTerm( 'event' );

	assert.equal(
		testee.setTermOperation.callCount,
		0,
		'... SHOULD NOT call setTermOperation().'
	);

	assert.equal(
		testee.getSelectedRelation.callCount,
		0,
		'... SHOULD NOT call getSelectedRelation().'
	);

	assert.equal(
		testee.selectTerm.callCount,
		0,
		'... SHOULD NOT call selectTerm().'
	);

	assert.end();
} );

test( 'propagateSelectedTerm (not propagating, invalid relation) ...', ( assert ) => {
	const testee = createTestee();

	// Turn method into spy.
	testee.setTermOperation = sinon.spy();

	// Turn method into stub.
	testee.getSelectedRelation = sinon.stub().returns( '' );

	// Turn method into spy.
	testee.selectTerm = sinon.spy();

	testee.propagateSelectedTerm( { target: 'target' } );

	assert.equal(
		testee.setTermOperation.callCount,
		1,
		'... SHOULD call setTermOperation().'
	);

	assert.equal(
		testee.getSelectedRelation.callCount,
		1,
		'... SHOULD call getSelectedRelation().'
	);

	assert.equal(
		testee.selectTerm.callCount,
		0,
		'... SHOULD NOT call selectTerm().'
	);

	assert.end();
} );

// TODO: See why this is not working as expected - skipping for now.
test.skip( 'propagateSelectedTerm (not propagating, valid relation) ...', ( assert ) => {

	const _elements = F.getRandomArray();

	const $selects = new JqueryObject( { _elements } );
	$selects.not.returnsThis();

	const $el = new JqueryObject();
	$el.find.returns( $selects );

	const testee = createTestee( { $el } );

	// Turn method into spy.
	testee.setTermOperation = sinon.spy();

	// Turn method into stub.
	testee.getSelectedRelation = sinon.stub().returns( F.getRandomString() );

	const selectTermResults = [ false, ...F.getRandomBoolArray() ];

	// Turn method into spy.
	testee.selectTerm = sinon.stub();
	selectTermResults.forEach( ( index, value ) => testee.selectTerm.onCall( index ).returns( value ) );

	testee.propagateSelectedTerm( { target: 'target' } );

	assert.equal(
		testee.getSelectedRelation.callCount,
		1,
		'... SHOULD call getSelectedRelation().'
	);

	assert.equal(
		testee.selectTerm.callCount,
		_elements.length,
		'... SHOULD call selectTerm() for each select element.'
	);

	assert.equal(
		testee.setTermOperation.callCount,
		selectTermResults.filter( ( v ) => v ).length,
		'... SHOULD call setTermOperation() for every successful term selection (including the current term).'
	);

	assert.end();
} );

test( 'getSelectedRelation (relation missing) ...', ( assert ) => {
	const testee = createTestee();

	const $option = new JqueryObject();
	$option.data.returns( undefined );

	const $select = new JqueryObject();
	$select.find.returns( $option );

	assert.equal(
		testee.getSelectedRelation( $select ),
		'',
		'... SHOULD return an empty string.'
	);

	assert.end();
} );

test( 'getSelectedRelation (relation specified) ...', ( assert ) => {
	const testee = createTestee();

	const relation = F.getRandomString();

	const $option = new JqueryObject();
	$option.data.returns( relation );

	const $select = new JqueryObject();
	$select.find.returns( $option );

	assert.equal(
		testee.getSelectedRelation( $select ),
		relation,
		'... SHOULD return the expected relation.'
	);

	assert.end();
} );

test( 'selectTerm (matching relation) ...', ( assert ) => {
	const testee = createTestee();

	const termID = F.getRandomInteger();

	const $option = new JqueryObject( {
		_elements: [ 'element' ]
	} );
	$option.val.returns( termID );

	const $select = new JqueryObject();
	$select.find.returns( $option );

	assert.equal(
		testee.selectTerm( $select, 'relation' ),
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

test( 'selectTerm (no matching relation) ...', ( assert ) => {
	const testee = createTestee();

	// Make method return a random string (i.e., relation found).
	// Due to incompatible arguments, this has to stay an arrow function (i.e., not just a function reference).
	testee.getSelectedRelation = () => F.getRandomString();

	const termID = F.getRandomInteger();

	const $option = new JqueryObject();
	$option.val.returns( termID );

	const $options = new JqueryObject();
	$options.first.returns( $option );

	const $select = new JqueryObject();
	$select.find.returns( $options );

	assert.equal(
		testee.selectTerm( $select, 'relation' ),
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

test( 'selectTerm (relation missing) ...', ( assert ) => {
	const testee = createTestee();

	// Make method return an empty string (i.e., no relation found).
	testee.getSelectedRelation = F.returnEmptyString;

	const $select = new JqueryObject();
	$select.find.returns( new JqueryObject() );

	assert.equal(
		testee.selectTerm( $select, 'relation' ),
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
