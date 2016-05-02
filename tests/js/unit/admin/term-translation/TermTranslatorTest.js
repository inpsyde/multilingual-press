import "../../stubs/global";
import test from "tape";
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

// TODO: Test propagateSelectedTerm (need to manipulate "private" property isPropagating via __Rewire__)...

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
