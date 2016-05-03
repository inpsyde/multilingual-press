import "../../stubs/global";
import test from "tape";
import sinon from "sinon";
import * as _ from "lodash";
import * as F from "../../functions";
import Backbone from "../../stubs/Backbone";
import jQueryObject from "../../stubs/jQueryObject";
import RelationshipControl from "../../../../../resources/js/admin/post-translation/RelationshipControl";

const { $, window } = global;

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
	Backbone.Events.on.reset();

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

test( 'findMetaBox ...', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.findMetaBox( 'metaBox' ),
		-1,
		'... SHOULD return -1 in case the meta box was not found.'
	);

	assert.end();
} );

test( 'findMetaBox ...', ( assert ) => {
	const testee = createTestee();

	const $metaBox = 'metaBox';

	const unsavedRelationships = F.getRandomArray();

	const index = F.getRandomInteger( 0, unsavedRelationships.length );

	unsavedRelationships[ index ] = $metaBox;

	RelationshipControl.__Rewire__( '_this', { unsavedRelationships } );

	assert.equal(
		testee.findMetaBox( $metaBox ),
		index,
		'... SHOULD return the expected index.'
	);

	RelationshipControl.__ResetDependency__( '_this' );

	assert.end();
} );

// TODO: confirmUnsavedRelationships

test( 'saveRelationship ...', ( assert ) => {
	Backbone.Events.trigger.reset();

	const options = {
		EventManager: Backbone.Events
	};

	const testee = createTestee( options );

	// Turn method into spy.
	testee.getEventName = sinon.spy();

	const event = {
		target: F.getRandomString()
	};

	const action = 'stay';

	const $input = new jQueryObject();
	$input.val.returns( action );

	$.returns( $input );

	testee.saveRelationship( event );

	assert.equal(
		testee.getEventName.callCount,
		1,
		'... SHOULD call getEventName().'
	);

	assert.equal(
		testee.getEventName.calledWith( action ),
		true,
		'... SHOULD call getEventName() with the expected action.'
	);

	assert.equal(
		options.EventManager.trigger.callCount,
		0,
		'... SHOULD NOT trigger any events in case nothing changed.'
	);

	// Restore jQuery.
	$.reset();

	assert.end();
} );

test( 'saveRelationship ...', ( assert ) => {
	Backbone.Events.trigger.reset();

	const options = {
		EventManager: Backbone.Events
	};

	const testee = createTestee( options );

	const eventName = F.getRandomString();

	// Turn method into spy.
	testee.getEventName = sinon.stub().returns( eventName );

	const event = {
		target: F.getRandomString()
	};

	const action = F.getRandomString();

	const $input = new jQueryObject();
	$input.val.returns( action );

	const $button = new jQueryObject();
	$button.data.returnsArg( 0 );

	$.returns( $input )
		.withArgs( event.target ).returns( $button );

	testee.saveRelationship( event );

	assert.equal(
		testee.getEventName.callCount,
		1,
		'... SHOULD call getEventName().'
	);

	assert.equal(
		testee.getEventName.calledWith( action ),
		true,
		'... SHOULD call getEventName() with the expected action.'
	);

	assert.equal(
		$button.prop.callCount,
		1,
		'... SHOULD call prop().'
	);

	assert.equal(
		$button.prop.calledWith( 'disabled', 'disabled' ),
		true,
		'... SHOULD disable the button.'
	);

	assert.equal(
		options.EventManager.trigger.callCount,
		1,
		'... SHOULD trigger an event.'
	);

	const args = options.EventManager.trigger.firstCall.args;

	assert.equal(
		args[ 0 ],
		'RelationshipControl:' + eventName,
		'... SHOULD trigger the expected event.'
	);

	const eventData = {
		action: 'mlp_rc_' + action,
		remote_post_id: 'remote-post-id',
		remote_site_id: 'remote-site-id',
		source_post_id: 'source-post-id',
		source_site_id: 'source-site-id'
	};

	assert.deepEqual(
		args[ 1 ],
		eventData,
		'... SHOULD pass along the expected data.'
	);

	assert.deepEqual(
		args[ 2 ],
		eventName,
		'... SHOULD pass along the expected event name.'
	);

	// Restore jQuery.
	$.reset();

	assert.end();
} );

test( 'getEventName ...', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.getEventName( 'search' ),
		'connectExistingPost',
		'... SHOULD return the expected result for "search".'
	);

	assert.end();
} );

test( 'getEventName ...', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.getEventName( 'new' ),
		'connectNewPost',
		'... SHOULD return the expected result for "new".'
	);

	assert.end();
} );

test( 'getEventName ...', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.getEventName( 'disconnect' ),
		'disconnectPost',
		'... SHOULD return the expected result for "disconnect".'
	);

	assert.end();
} );

test( 'getEventName ...', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.getEventName(),
		'',
		'... SHOULD return an empty string for any unknown action.'
	);

	assert.end();
} );

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

	// Restore jQuery.
	$.reset();

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

test( 'connectExistingPost ...', ( assert ) => {
	const testee = createTestee();

	window.alert.reset();

	// Turn method in to spy.
	testee.sendRequest = sinon.spy();

	const postID = F.getRandomInteger( 1 );

	const data = F.getRandomObject();
	data.remote_site_id = F.getRandomInteger();

	const $input = new jQueryObject();
	$input.val.returns( postID );

	$.returns( $input );

	assert.equal(
		testee.connectExistingPost( data ),
		true,
		'... SHOULD return the expected result.'
	);

	assert.equal(
		window.alert.callCount,
		0,
		'... SHOULD NOT show an alert.'
	);

	assert.equal(
		testee.sendRequest.callCount,
		1,
		'... SHOULD call sendRequest() in case of a checked post input.'
	);

	// Manipulate data object for subsequent test.
	data.new_post_id = postID;

	assert.deepEqual(
		testee.sendRequest.firstCall.args[ 0 ],
		data,
		'... SHOULD pass the expected data to sendRequest() in case of a checked post input.'
	);

	// Restore global scope.
	$.reset();

	assert.end();
} );

test( 'connectExistingPost ...', ( assert ) => {
	const settings = {
		L10n: {
			noPostSelected: ''
		}
	};

	const testee = createTestee( { settings } );

	window.alert.reset();

	// Turn method in to spy.
	testee.sendRequest = sinon.spy();

	const $input = new jQueryObject();
	$input.val.returns( '' );

	$.returns( $input );

	const data = F.getRandomObject();
	data.remote_site_id = F.getRandomInteger();

	assert.equal(
		testee.connectExistingPost( data ),
		false,
		'... SHOULD return the expected result.'
	);

	assert.equal(
		window.alert.callCount,
		1,
		'... SHOULD show an alert.'
	);

	assert.equal(
		testee.sendRequest.callCount,
		0,
		'... SHOULD NOT call sendRequest() in case of no checked post input.'
	);

	// Restore global scope.
	$.reset();

	assert.end();
} );

test( 'sendRequest ...', ( assert ) => {
	const Util = {
		reloadLocation: F.getRandomString()
	};

	const testee = createTestee( { Util } );

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
		url: window.ajaxurl
	};

	assert.deepEqual(
		$.ajax.firstCall.args[ 0 ],
		ajaxData,
		'... SHOULD pass along the expected data.'
	);

	assert.end();
} );
