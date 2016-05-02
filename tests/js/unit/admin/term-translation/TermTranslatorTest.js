import "../../stubs/global";
import test from "tape";
import sinon from "sinon";
import * as F from "../../functions";
import jQueryObject from "../../stubs/jQueryObject";
import TermTranslator from "../../../../../resources/js/admin/term-translation/TermTranslator";

test( '$selects ...', ( assert ) => {
	const $selects = F.getRandomString();

	const options = {
		$el: new jQueryObject( {
			find: () => $selects
		} )
	};

	const testee = new TermTranslator( options );

	assert.equal(
		testee.$selects,
		$selects,
		'... SHOULD have the expected value.'
	);

	assert.end();
} );

test( 'propagateSelectedTerm ...', ( assert ) => {
	const testee = new TermTranslator();

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
		'... SHOULD NOT call getSelectedRelation() in case of an ongoing term propagation.'
	);

	assert.equal(
		testee.selectTerm.callCount,
		0,
		'... SHOULD NOT call selectTerm() in case of an ongoing term propagation.'
	);

	// Restore internal data.
	TermTranslator.__ResetDependency__( '_this' );

	assert.end();
} );

test( 'propagateSelectedTerm ...', ( assert ) => {
	const testee = new TermTranslator();

	// Turn method into stub.
	testee.getSelectedRelation = sinon.stub().returns( '' );

	// Turn method into spy.
	testee.selectTerm = sinon.spy();

	testee.propagateSelectedTerm( { target: 'target' } );

	assert.equal(
		testee.getSelectedRelation.callCount,
		1,
		'... SHOULD call getSelectedRelation() in case of no ongoing term propagation.'
	);

	assert.equal(
		testee.selectTerm.callCount,
		0,
		'... SHOULD NOT call selectTerm() in case of no valid relation.'
	);

	assert.end();
} );

test( 'propagateSelectedTerm ...', ( assert ) => {
	const _elements = F.getRandomArray();

	const $selects = new jQueryObject( { _elements } );
	$selects.not.returns( $selects );

	const $el = new jQueryObject();
	$el.find.returns( $selects );

	const testee = new TermTranslator( { $el } );

	// Turn method into stub.
	testee.getSelectedRelation = sinon.stub().returns( F.getRandomString() );

	// Turn method into spy.
	testee.selectTerm = sinon.spy();

	testee.propagateSelectedTerm( { target: 'target' } );

	assert.equal(
		testee.getSelectedRelation.callCount,
		1,
		'... SHOULD call getSelectedRelation() in case of no ongoing term propagation.'
	);

	assert.equal(
		testee.selectTerm.callCount,
		_elements.length,
		'... SHOULD NOT call selectTerm() in case of no valid relation.'
	);

	assert.end();
} );

test( 'getSelectedRelation ...', ( assert ) => {
	const testee = new TermTranslator();

	const $option = new jQueryObject();
	$option.data.returns( undefined );

	const $select = new jQueryObject();
	$select.find.returns( $option );

	assert.equal(
		testee.getSelectedRelation( $select ),
		'',
		'... SHOULD return an empty string for a missing relation.'
	);

	assert.end();
} );

test( 'getSelectedRelation ...', ( assert ) => {
	const testee = new TermTranslator();

	const relation = F.getRandomString();

	const $option = new jQueryObject();
	$option.data.returns( relation );

	const $select = new jQueryObject();
	$select.find.returns( $option );

	assert.equal(
		testee.getSelectedRelation( $select ),
		relation,
		'... SHOULD return the expected relation for an existing relation.'
	);

	assert.end();
} );

test( 'selectTerm ...', ( assert ) => {
	const testee = new TermTranslator();

	const termID = F.getRandomInteger();

	const $option = new jQueryObject( {
		_elements: [ 'element' ]
	} );
	$option.val.returns( termID );

	const $select = new jQueryObject();
	$select.find.returns( $option );

	testee.selectTerm( $select, 'relation' );

	assert.equal(
		$select.val.callCount,
		1,
		'... SHOULD set a term value for a matching relation.'
	);

	assert.equal(
		$select.val.calledWith( termID ),
		true,
		'... SHOULD set the expected term value for a matching relation.'
	);

	assert.end();
} );

test( 'selectTerm ...', ( assert ) => {
	const testee = new TermTranslator();

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

	testee.selectTerm( $select, 'relation' );

	assert.equal(
		$select.val.callCount,
		1,
		'... SHOULD set a term value for a not-matching relation.'
	);

	assert.equal(
		$select.val.calledWith( termID ),
		true,
		'... SHOULD set the expected term value for a not-matching relation.'
	);

	assert.end();
} );

test( 'selectTerm ...', ( assert ) => {
	const testee = new TermTranslator();

	// Make method return an empty string (i.e., no relation found).
	testee.getSelectedRelation = F.returnEmptyString;

	const $select = new jQueryObject();
	$select.find.returns( new jQueryObject() );

	testee.selectTerm( $select, 'relation' );

	assert.equal(
		$select.val.callCount,
		0,
		'... SHOULD NOT set a term value for a missing relation.'
	);

	assert.end();
} );
