import "../../stubs/global";
import test from "tape";
import sinon from "sinon";
import * as F from "../../functions";
import Registry from "../../../../../resources/js/admin/core/Registry";

const createTestee = () => {
	const router = sinon.stub();
	router.route = sinon.spy();

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

	testee.createModule( data );

	assert.equal(
		data.Constructor.callCount,
		1,
		'... SHOULD create one module instance.'
	);

	assert.equal(
		data.Constructor.calledWith( data.options ),
		true,
		'... SHOULD pass the expected options to the module constructor.'
	);

	if ( data.callback ) {
		assert.equal(
			data.callback.callCount,
			1,
			'... SHOULD fire a callback IF it was passed.'
		);
	}

	assert.end();
} );

// TODO: Test for createModules (plural).

test( 'initializeRoute ...', ( assert ) => {
	const testee = createTestee();

	// Turn method into spy.
	testee.createModules = sinon.spy();

	const router = {
		route: sinon.spy()
	};

	Registry.__Rewire__( '_this', { router } );

	const route = F.getRandomString();

	const modules = F.getRandomString();

	testee.initializeRoute( route, modules );

	assert.equal(
		router.route.callCount,
		1,
		'... SHOULD route once.'
	);

	assert.equal(
		// The third argument (i.e., the callback) is missing because it is an (arrow) function, which sinon cannot handle.
		router.route.calledWith( route, route ),
		true,
		'... SHOULD pass the expected arguments to router.route().'
	);

	// Execute the callback passed as third argument.
	router.route.firstCall.args[ 2 ]();

	assert.equal(
		testee.createModules.callCount,
		1,
		'... SHOULD pass the expected callback to router.route.'
	);

	assert.equal(
		testee.createModules.calledWith( modules ),
		true,
		'... SHOULD pass the expected modules to the callback.'
	);

	Registry.__ResetDependency__( '_this' );

	assert.end();
} );

test( 'initializeRoutes ...', ( assert ) => {
	const testee = createTestee();

	// Turn method into spy.
	testee.initializeRoute = sinon.spy();

	const data = F.getRandomObject();

	const modules = F.getRandomString();

	Registry.__Rewire__( '_this', {
		data,
		modules
	} );

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

	Registry.__ResetDependency__( '_this' );

	assert.end();
} );

test( 'registerModuleForRoute ...', ( assert ) => {
	const testee = createTestee();

	const route = F.getRandomString();

	const data = {};
	data[ route ] = F.getRandomArray();

	Registry.__Rewire__( '_this', { data } );

	assert.equal(
		testee.registerModuleForRoute( module, route ),
		data[ route ].length + 1,
		'... SHOULD return the expected result.'
	);

	Registry.__ResetDependency__( '_this' );

	assert.end();
} );
