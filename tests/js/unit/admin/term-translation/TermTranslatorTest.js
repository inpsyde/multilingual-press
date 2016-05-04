import "../../stubs/global";
import test from "tape";
import sinon from "sinon";
import * as _ from "lodash";
import * as F from "../../functions";
import jQueryObject from "../../stubs/jQueryObject";
import TermTranslator from "../../../../../resources/js/admin/term-translation/TermTranslator";

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
		$el: new jQueryObject( {
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
	testee.getSelectedRelation = sinon.spy();

	// Turn method into spy.
	testee.selectTerm = sinon.spy();

	testee.propagateSelectedTerm( 'event' );

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

	// Turn method into stub.
	testee.getSelectedRelation = sinon.stub().returns( '' );

	// Turn method into spy.
	testee.selectTerm = sinon.spy();

	testee.propagateSelectedTerm( { target: 'target' } );

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

test( 'propagateSelectedTerm (not propagating, valid relation) ...', ( assert ) => {
	const _elements = F.getRandomArray();

	const $selects = new jQueryObject( { _elements } );
	$selects.not.returnsThis();

	const $el = new jQueryObject();
	$el.find.returns( $selects );

	const testee = createTestee( { $el } );

	// Turn method into stub.
	testee.getSelectedRelation = sinon.stub().returns( F.getRandomString() );

	// Turn method into spy.
	testee.selectTerm = sinon.spy();

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

	assert.end();
} );

test( 'getSelectedRelation (relation missing) ...', ( assert ) => {
	const testee = createTestee();

	const $option = new jQueryObject();
	$option.data.returns( undefined );

	const $select = new jQueryObject();
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

	const $option = new jQueryObject();
	$option.data.returns( relation );

	const $select = new jQueryObject();
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

	const $option = new jQueryObject( {
		_elements: [ 'element' ]
	} );
	$option.val.returns( termID );

	const $select = new jQueryObject();
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
	// Due to incompatible arguments, this has to stay an arrow function (i..e, not just a function reference).
	testee.getSelectedRelation = () => F.getRandomString();

	const termID = F.getRandomInteger();

	const $option = new jQueryObject();
	$option.val.returns( termID );

	const $options = new jQueryObject();
	$options.first.returns( $option );

	const $select = new jQueryObject();
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

	const $select = new jQueryObject();
	$select.find.returns( new jQueryObject() );

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
