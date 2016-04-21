import test from "tape";
import sinon from "sinon";
import * as F from "../../functions";
import Registry from "../../../../resources/js/admin/core/Registry";
import Router from "../../../../resources/js/admin/core/Router";
Router.__Rewire__( 'Backbone', require( 'backbone' ) );
const createTestee = ( router ) => {
	router = router || new Router();

	return new Registry( router );
};

test( 'Registry is a constructor function', ( assert ) => {
	assert.equal(
		typeof Registry,
		'function',
		'Registry SHOULD be a function.' );

	assert.equal(
		typeof createTestee(),
		'object',
		'Registry SHOULD construct an object.'
	);

	assert.end();
} );

test( 'createModule behaves as expected', ( assert ) => {
	const testee = createTestee();

	let ModuleMock = sinon.spy();

	const moduleData = {
		Constructor: ModuleMock,
		options    : {},
		callback   : sinon.spy()
	};

	testee.createModule( moduleData );

	assert.ok(
		testee.modules[ ModuleMock.name ] instanceof ModuleMock,
		'createModule should register an instance of the Module by its name.'
	);

	assert.equal(
		moduleData.callback.callCount,
		1,
		'Module callback should have been fired once' );
	assert.end();
} );

test( 'initializeRoute behaves as expected', ( assert ) => {
	const testee = createTestee();
	const route = 'testroute';
	const modules = [ {} ];
	// How do we actually test this?
	testee.initializeRoute( route, modules );

	assert.end();
} );

test( 'initializeRoutes behaves as expected', ( assert ) => {
	const testee = createTestee();
	
	// How do we actually test this?
	testee.initializeRoutes();

	assert.end();
} );

test( 'registerModuleForRoute behaves as expected', ( assert ) => {
	const testee = createTestee(),
		route = 'testroute',
		module = {};

	let result = testee.registerModuleForRoute( module, route );

	assert.ok(
		testee.data.hasOwnProperty( route ),
		'Registry data SHOULD contain an array under the given key.'
	);

	assert.equal(
		typeof result,
		'number',
		'Return type should be the numeric.'
	);

	assert.notEqual(
		testee.data[ route ].indexOf( module ),
		-1,
		'Registry data array SHOULD contain the given module.'
	);

	assert.end();
} );

