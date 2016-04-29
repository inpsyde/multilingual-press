import "../../stubs/global";
import test from "tape";
import sinon from "sinon";
import * as F from "../../functions";
import jQueryObject from "../../stubs/jQueryObject";
import { Toggler } from "../../../../../resources/js/admin/core/common";

test( 'initializeStateToggler...', ( assert ) => {

		const testee = new Toggler();

		const element = F.getRandomString();

		const $stub = new jQueryObject();

		$.returns( $stub );

		testee.initializeStateToggler( element );

		assert.equal(
			$stub.on.callCount,
			1,
			'... SHOULD have added 1 event handler'
		);

		assert.end();
	}
);

test( 'initializeStateTogglers...', ( assert ) => {
		const testee = new Toggler();

		const element = F.getRandomString();

		const _elements = F.getRandomArray( 1, 10, element );

		$.withArgs( '.mlp-state-toggler' ).returns( new jQueryObject( { _elements } ) );

		testee.initializeStateToggler = sinon.spy();

		testee.initializeStateTogglers();

		assert.equal(
			testee.initializeStateToggler.callCount,
			_elements.length,
			'... SHOULD call initializeStateToggler once for each jQuery element'
		);

		assert.end();
	}
);

test( 'toggleElement...', ( assert ) => {
		const testee = new Toggler();

		const target = F.getRandomString();

		const event = {
			target
		};
		// Create 2 jQuery stubs for different selector calls
		const $dataStub = new jQueryObject();

		const $toggleStub = new jQueryObject();

		const targetID = F.getRandomBool() ? F.getRandomString() : false;

		$dataStub.data.returns( targetID );

		// Wire up the jQuery stubs with the appropriate selectors
		$.withArgs( target ).returns( $dataStub );
		$.withArgs( targetID ).returns( $toggleStub );

		const result = testee.toggleElement( event );

		assert.equal(
			result,
			!!targetID,
			'... SHOULD return whether or not it has toggled an element'
		);

		if ( targetID ) {
			assert.equal(
				$toggleStub.toggle.callCount,
				1,
				'... SHOULD call toggle() on the jQuery element if a targetID was found'
			);
		}

		assert.end();
	}
);