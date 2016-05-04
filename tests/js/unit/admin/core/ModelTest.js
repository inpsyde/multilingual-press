import "../../stubs/global";
import test from "tape";
import * as F from "../../functions";
import Model from "../../../../../resources/js/admin/core/Model";

test( 'constructor ...', ( assert ) => {
	const urlRoot = F.getRandomString();

	const testee = new Model( { urlRoot } );

	assert.equal(
		testee.urlRoot,
		urlRoot,
		'... SHOULD set the expected URL root.'
	);

	assert.end();
} );
