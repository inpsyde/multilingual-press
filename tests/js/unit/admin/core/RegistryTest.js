import "../../stubs/global";
import test from "tape";
import sinon from "sinon";
import Registry from "../../../../../resources/js/admin/core/Registry";

const createTestee = ( router ) => {
	if ( !router ) {
		router = sinon.stub();
		router.prototype.route = sinon.spy();
	}

	return new Registry( new router() );
};

// TODO: Adapt test to new private properties...

test( 'Registry is a constructor function', ( assert ) => {
		assert.equal(
			typeof Registry,
			'function',
			'Registry SHOULD be a function.'
		);

		assert.equal(
			typeof createTestee(),
			'object',
			'Registry SHOULD construct an object.'
		);

		assert.end();
	}
);

test( 'createModule...', ( assert ) => {

		const testee = createTestee();

		let ModuleMock = sinon.stub();

		const moduleData = {
			Constructor: ModuleMock,
			options    : {},
			callback   : sinon.spy()
		};

		testee.createModule( moduleData );

		assert.equal(
			moduleData.callback.callCount,
			1,
			'....SHOULD fire a callback if it was passed'
		);
		assert.end();
	}
);

test( 'initializeRoute...', ( assert ) => {
		const testee = createTestee();
		const route = 'testroute';
		const modules = [ {} ];

		const routeSpy = sinon.spy();
		Registry.__Rewire__( '_this', {
				router: {
					route: routeSpy
				}
			}
		);

		testee.initializeRoute( route, modules );

		assert.equal(
			routeSpy.callCount,
			1,
			'Router should have routed once'
		);

		Registry.__ResetDependency__( '_this' );
		assert.end();
	}
);

test( 'initializeRoutes...', ( assert ) => {
		const testee = createTestee();

		Registry.__Rewire__( '_this', {
				data: { 'testroute_1': {}, 'testroute_2': {} }
			}
		);

		// We don't care about what this method does, just about the fact that it's being called
		testee.initializeRoute = sinon.spy();

		// Create a simple mock for $.each()
		$.each = ( o = {}, c ) => {
			for ( let k in o ) {
				if ( o.hasOwnProperty( k ) ) {
					c( k, o[ k ] );
				}
			}
		};

		testee.initializeRoutes();

		assert.equal(
			testee.initializeRoute.callCount,
			2,
			'...SHOULD call initializeRoute() for each passed route'
		);

		assert.end();
	}
);

test( 'registerModuleForRoute...', ( assert ) => {
		const testee = createTestee(),
			route = 'testroute',
			module = {};

		let result = testee.registerModuleForRoute( module, route );

		assert.equal(
			typeof result,
			'number',
			'...SHOULD return a number.'
		);

		assert.end();
	}
);

