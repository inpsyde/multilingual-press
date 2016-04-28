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

// TODO: Test propagateSelectedTerm (need to manipulate "private" property isPropagating somehow)...

test( 'getSelectedRelation ...', ( assert ) => {
	const testee = new TermTranslator();

	const $select = new jQueryObject();
	$select.find.returns( {
		data: F.returnUndefined
	} );

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

	const $select = new jQueryObject();
	$select.find.returns( {
		data: () => relation
	} );

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

	const $select = new jQueryObject();
	$select.find.returns(
		new jQueryObject( {
			val: () => termID
		} )
	);

	testee.selectTerm( $select, 'relation' );

	assert.equal(
		$select.val.callCount,
		1,
		'... SHOULD set a term value for an existing relation.'
	);

	assert.equal(
		$select.val.calledWith( termID ),
		true,
		'... SHOULD set the expected term value for an existing relation.'
	);

	assert.end();
} );

// TODO: Test selectTerm (need to mock testee.getSelectedRelation in one case)...

// TODO: Recheck why creating a new jQueryObject doesn't create a fresh state (of the spies, stubs and the like)...!
test.skip( 'selectTerm ...', ( assert ) => {
	const testee = new TermTranslator();

	// Make method return an empty string (i.e., no relation found).
	testee.getSelectedRelation = F.returnEmptyString;

	const $select = new jQueryObject();
	$select.find.returns( [] );

	testee.selectTerm( $select, 'relation' );

	assert.equal(
		$select.val.callCount,
		0,
		'... SHOULD NOT set a term value for a missing relation.'
	);

	assert.end();
} );
