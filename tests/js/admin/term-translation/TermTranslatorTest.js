import "../../stubs/global";
import test from "tape";
import * as F from "../../functions";
import jQueryObject from "../../stubs/jQueryObject";
import TermTranslator from "../../../../resources/js/admin/term-translation/TermTranslator";

test( '$selects behaves as expected', ( assert ) => {
	const $selects = F.getRandomString();

	const $el = new jQueryObject( {
		find: () => $selects
	} );

	const testee = new TermTranslator( { $el } );

	assert.equal(
		testee.$selects,
		$selects,
		'$selects SHOULD have the expected value.'
	);

	assert.end();
} );

// TODO: Test propagateSelectedTerm (need to manipulate "private" property isPropagating somehow)...

test( 'getSelectedRelation behaves as expected for a missing relation', ( assert ) => {
	const testee = new TermTranslator();

	const $select = new jQueryObject();
	$select.find.returns( { data: F.returnUndefined } );

	assert.equal(
		testee.getSelectedRelation( $select ),
		'',
		'getSelectedRelation SHOULD return an empty string for a missing relation.'
	);

	assert.end();
} );

test( 'getSelectedRelation behaves as expected for an existing relation', ( assert ) => {
	const testee = new TermTranslator();

	const relation = F.getRandomString();

	const $select = new jQueryObject();
	$select.find.returns( { data: () => relation } );

	assert.equal(
		testee.getSelectedRelation( $select ),
		relation,
		'getSelectedRelation SHOULD return the expected relation.'
	);

	assert.end();
} );

// TODO: Test selectTerm (need to mock testee.getSelectedRelation in one case)...
