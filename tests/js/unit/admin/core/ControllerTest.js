import globalStub from "../../stubs/global";
import test from "tape";
import sinon from "sinon";
import * as F from "../../functions";
import Controller from "../../../../../resources/js/admin/core/Controller";

const { Backbone } = global;

/**
 * Returns a new instance of the class under test.
 * @param {Registry} [registry] - Optional. The registry object. Defaults to a Sinon.JS stub.
 * @param {Object} [settings={}] - Optional. The controller settings. Defaults to an empty object.
 * @returns {Controller} The instance of the class under test.
 */
const createTestee = ( registry = sinon.stub(), settings = {} ) => {
	// Rewire internal data.
	Controller.__Rewire__( '_this', {} );

	return new Controller( registry, settings );
};

test( 'settings ...', ( assert ) => {
	const settings = F.getRandomString();

	const testee = createTestee( null, settings );

	assert.equal(
		testee.settings,
		settings,
		'... SHOULD have the expected value.'
	);

	assert.end();
} );

test( 'initialize ...', ( assert ) => {
	const modules = F.getRandomString();

	const registry = {
		initializeRoutes: sinon.stub().returns( modules )
	};

	const testee = createTestee( registry );

	// Turn method into spy.
	testee.maybeStartHistory = sinon.spy();

	assert.equal(
		testee.initialize(),
		modules,
		'... SHOULD return the expected modules.'
	);

	assert.equal(
		testee.maybeStartHistory.callCount,
		1,
		'... SHOULD (maybe) start the Backbone history.'
	);

	assert.end();
} );

test( 'maybeStartHistory (history not yet started) ...', ( assert ) => {
	const settings = {
		urlRoot: F.getRandomString()
	};

	const testee = createTestee( null, settings );

	assert.equal(
		testee.maybeStartHistory(),
		true,
		'... SHOULD return true.'
	);

	const options = {
		root: settings.urlRoot,
		pushState: true,
		hashChange: false
	};

	assert.equal(
		Backbone.history.start.calledWith( options ),
		true,
		'... SHOULD start the Backbone history.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );

test( 'maybeStartHistory (history already started) ...', ( assert ) => {
	// Manipulate the Backbone history.
	Backbone.History.started = true;

	const testee = createTestee();

	assert.equal(
		testee.maybeStartHistory(),
		false,
		'... SHOULD return false.'
	);

	assert.equal(
		Backbone.history.start.callCount,
		0,
		'... SHOULD NOT start the Backbone history.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );

test( 'registerModule (no route) ...', ( assert ) => {
	const registry = {
		registerModuleForRoute: sinon.stub()
	};

	const testee = createTestee( registry );

	const Constructor = F.getRandomString();

	testee.registerModule( [], Constructor );

	assert.equal(
		registry.registerModuleForRoute.callCount,
		0,
		'... SHOULD NOT register any modules.'
	);

	assert.end();
} );

test( 'registerModule (one route) ...', ( assert ) => {
	const registry = {
		registerModuleForRoute: sinon.stub()
	};

	const testee = createTestee( registry );

	const route = F.getRandomString();

	const Constructor = F.getRandomString();

	const options = F.getRandomString();

	const callback = F.getRandomString();

	testee.registerModule( route, Constructor, options, callback );

	assert.equal(
		registry.registerModuleForRoute.callCount,
		1,
		'... SHOULD register a module for one route.'
	);

	const moduleData = {
		Constructor,
		options,
		callback
	};

	assert.equal(
		registry.registerModuleForRoute.calledWith( moduleData, route ),
		true,
		'... SHOULD register the expected module.'
	);

	assert.end();
} );

test( 'registerModule (multiple routes) ...', ( assert ) => {
	const registry = {
		registerModuleForRoute: sinon.stub()
	};

	const testee = createTestee( registry );

	const route = F.getRandomString();

	const routes = F.getRandomArray( 1, 10, route );

	const Constructor = F.getRandomString();

	const options = F.getRandomString();

	const callback = F.getRandomString();

	testee.registerModule( routes, Constructor, options, callback );

	assert.equal(
		registry.registerModuleForRoute.callCount,
		routes.length,
		'... SHOULD register a module for each route.'
	);

	const moduleData = {
		Constructor,
		options,
		callback
	};

	assert.equal(
		registry.registerModuleForRoute.calledWith( moduleData, route ),
		true,
		'... SHOULD register the expected module.'
	);

	assert.end();
} );
