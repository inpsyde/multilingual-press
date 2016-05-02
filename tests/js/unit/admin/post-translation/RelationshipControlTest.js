import "../../stubs/global";
import test from "tape";
import sinon from "sinon";
import * as _ from "lodash";
import * as F from "../../functions";
import Backbone from "../../stubs/Backbone";
import jQueryObject from "../../stubs/jQueryObject";
import RelationshipControl from "../../../../../resources/js/admin/post-translation/RelationshipControl";

const { $ } = global;

/**
 * Returns a new instance of the class under test.
 * @param {Object} [options] - Optional. The constructor options.
 * @returns {RelationshipControl} The instance of the class under test.
 */
const createTestee = ( options ) => new RelationshipControl( _.extend( { settings: {} }, options ) );

test( 'settings ...', ( assert ) => {
	const options = {
		settings: F.getRandomString()
	};

	const testee = createTestee( options );

	assert.equal(
		testee.settings,
		options.settings,
		'... SHOULD have the expected value.'
	);

	assert.end();
} );

test( 'initializeEventHandlers ...', ( assert ) => {
	const options = {
		EventManager: Backbone.Events
	};

	const testee = createTestee( options );

	testee.initializeEventHandlers();

	assert.equal(
		options.EventManager.on.callCount,
		1,
		'... SHOULD attach callbacks.'
	);

	const callbacks = {
		'RelationshipControl:connectExistingPost': testee.connectExistingPost,
		'RelationshipControl:connectNewPost': testee.connectNewPost,
		'RelationshipControl:disconnectPost': testee.disconnectPost
	};

	assert.deepEqual(
		options.EventManager.on.firstCall.args[ 0 ],
		callbacks,
		'... SHOULD attach the expected callbacks.'
	);

	assert.end();
} );

// TODO: More tests...
