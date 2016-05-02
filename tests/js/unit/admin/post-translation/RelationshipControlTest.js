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

// TODO: updateUnsavedRelationships

// TODO: findMetaBox

// TODO: confirmUnsavedRelationships

// TODO: saveRelationship

// TODO: getEventName

test( 'connectNewPost ...', ( assert ) => {
	const testee = createTestee();

	// Turn method in to spy.
	testee.sendRequest = sinon.spy();

	const postTitle = F.getRandomString();

	const $postTitle = new jQueryObject();
	$postTitle.val.returns( postTitle );

	$.withArgs( 'input[name="post_title"]' ).returns( $postTitle );

	const data = F.getRandomObject();

	testee.connectNewPost( data );

	assert.equal(
		testee.sendRequest.callCount,
		1,
		'... SHOULD call sendRequest().'
	);

	// Manipulate data object for subsequent test.
	data.new_post_title = postTitle;

	assert.deepEqual(
		testee.sendRequest.firstCall.args[ 0 ],
		data,
		'... SHOULD pass the expected data to sendRequest().'
	);

	assert.end();
} );

test( 'disconnectPost ...', ( assert ) => {
	const testee = createTestee();

	// Turn method in to spy.
	testee.sendRequest = sinon.spy();

	const data = F.getRandomString();

	testee.disconnectPost( data );

	assert.equal(
		testee.sendRequest.callCount,
		1,
		'... SHOULD call sendRequest().'
	);

	assert.equal(
		testee.sendRequest.calledWith( data ),
		true,
		'... SHOULD pass the expected data to sendRequest().'
	);

	assert.end();
} );

// TODO: connectExistingPost

test( 'sendRequest ...', ( assert ) => {
	const Util = {
		reloadLocation: F.getRandomString()
	};

	const testee = createTestee( { Util } );

	const url = window.ajaxurl = F.getRandomString();

	const data = F.getRandomString();

	testee.sendRequest( data );

	assert.equal(
		$.ajax.callCount,
		1,
		'... SHOULD send an AJAX request.'
	);

	const ajaxData = {
		async: false,
		data,
		success: Util.reloadLocation,
		type: 'POST',
		url
	};

	assert.deepEqual(
		$.ajax.firstCall.args[ 0 ],
		ajaxData,
		'... SHOULD pass along the expected data.'
	);

	assert.end();
} );
