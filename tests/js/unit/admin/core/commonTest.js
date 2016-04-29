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

		//TODO

		assert.end();
	}
);