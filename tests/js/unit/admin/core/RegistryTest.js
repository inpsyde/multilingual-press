import "../../stubs/global";
import test from "tape";
import sinon from "sinon";
import * as F from "../../functions";
import Registry from "../../../../../resources/js/admin/core/Registry";

/**
 * Returns a new instance of the class under test.
 * @param {Router} [router] - Optional The router object. Defaults to a Sinon.JS stub.
 * @returns {Registry} The instance of the class under test.
 */
const createTestee = ( router = sinon.stub() ) => {
	// Rewire internal data.
	Registry.__Rewire__( '_this', {
		data: {},
		modules: {}
	} );

	return new Registry( router );
};

test( 'createModule ...', ( assert ) => {
	const testee = createTestee();

	const data = {
		Constructor: sinon.spy(),
		options: F.getRandomString()
	};

	// Maybe add a callback...
	if ( F.getRandomBool() ) {
		data.callback = sinon.spy();
	}

	const module = testee.createModule( data );

	assert.equal(
		data.Constructor.calledWith( data.options ),
		true,
		'... SHOULD create the expected module instance.'
	);

	if ( data.callback ) {
		assert.equal(
			data.callback.callCount,
			1,
			'... SHOULD fire the expected callback IF it was passed.'
		);
	}

	assert.equal(
		module instanceof data.Constructor,
		true,
		'... SHOULD return the expected module instance.'
	);

	assert.end();
} );

test( 'createModules ...', ( assert ) => {
	const testee = createTestee();

	// Turn method into spy.
	testee.createModule = sinon.spy();

	const modules = F.getRandomArray();

	testee.createModules( modules );

	assert.equal(
		testee.createModule.callCount,
		modules.length,
		'... SHOULD create an instance of each module.'
	);

	assert.end();
} );

// TODO: Check this!
test( 'initializeRoute ...', ( assert ) => {
	const router = {
		route: sinon.spy()
	};

	const testee = createTestee( router );

	// Turn method into spy.
	testee.createModules = sinon.spy();

	const route = F.getRandomString();

	const modules = F.getRandomString();

	testee.initializeRoute( route, modules );

	assert.equal(
		// The third argument (i.e., the callback) is missing as it is an (arrow) function, which sinon cannot handle.
		router.route.calledWith( route, route ),
		true,
		'... SHOULD route once, and pass along the expected arguments.'
	);

	// Execute the callback passed as third argument.
	router.route.firstCall.args[ 2 ]();

	assert.equal(
		testee.createModules.calledWith( modules ),
		true,
		'... SHOULD create the expected modules.'
	);

	assert.end();
} );

test( 'initializeRoutes ...', ( assert ) => {
	const testee = createTestee();

	const data = F.getRandomObject();

	const modules = F.getRandomString();

	// Rewire internal data.
	Registry.__Rewire__( '_this', {
		data,
		modules
	} );

	// Turn method into spy.
	testee.initializeRoute = sinon.spy();

	assert.equal(
		testee.initializeRoutes(),
		modules,
		'... SHOULD return the expected modules.'
	);

	assert.equal(
		testee.initializeRoute.callCount,
		Object.keys( data ).length,
		'... SHOULD initialize each passed route.'
	);

	assert.end();
} );

test( 'registerModuleForRoute ...', ( assert ) => {
	const testee = createTestee();

	const route = F.getRandomString();

	const data = {};
	data[ route ] = F.getRandomArray();

	const numRoutes = data[ route ].length;

	// Rewire internal data.
	Registry.__Rewire__( '_this', { data } );

	assert.equal(
		testee.registerModuleForRoute( module, route ),
		numRoutes + 1,
		'... SHOULD return the expected result.'
	);

	assert.end();
} );
