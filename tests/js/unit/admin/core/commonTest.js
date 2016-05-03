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
		const testee = new Toggler(),

			target = F.getRandomString(),

			event = { target },
		// Create 2 jQuery stubs for different selector calls
			$dataStub = new jQueryObject(),

			$toggleStub = new jQueryObject(),

			targetID = F.getRandomBool() ? F.getRandomString() : false;

		$dataStub.data.returns( targetID );

		// Wire up the jQuery stubs with the appropriate selectors
		$.withArgs( target ).returns( $dataStub );
		$.withArgs( targetID ).returns( $toggleStub );

		testee.toggleElement( event );

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

test( 'toggleElementIfChecked...', ( assert ) => {
		const testee = new Toggler(),

			$toggler = new jQueryObject(),

			data = { $toggler },

			event = { data },

			$toggleStub = new jQueryObject(),

			targetID = F.getRandomBool() ? F.getRandomString() : false,

			checked = F.getRandomBool();

		$toggler.data.returns( targetID );

		$toggler.is.returns( checked );

		$.withArgs( targetID ).returns( $toggleStub );

		testee.toggleElementIfChecked( event );

		if ( targetID ) {
			assert.equal(
				$toggleStub.toggle.calledWith( checked ),
				true,
				'... SHOULD toggle the element accoding to the checked state IF a target ID was found.'
			);
		}

		assert.end();
	}
);
