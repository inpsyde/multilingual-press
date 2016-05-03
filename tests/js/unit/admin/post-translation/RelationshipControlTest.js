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
const createTestee = ( options ) => {
	// Rewire internal data.
	RelationshipControl.__Rewire__( '_this', {
		unsavedRelationships: []
	} );

	return new RelationshipControl( _.extend( { settings: {} }, options ) );
};

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

test( 'updateUnsavedRelationships (changed input, meta box already stored) ...', ( assert ) => {
	const testee = createTestee();

	const unsavedRelationships = [ 'metaBox' ];

	// Rewire internal data.
	RelationshipControl.__Rewire__( '_this', { unsavedRelationships } );

	// Turn method into spy.
	testee.findMetaBox = sinon.spy();

	const event = {
		target: F.getRandomString()
	};

	const $input = new jQueryObject();
	$input.closest.returns( new jQueryObject() );

	$.withArgs( event.target ).returns( $input );

	assert.deepEqual(
		testee.updateUnsavedRelationships( event ),
		unsavedRelationships,
		'... SHOULD return the unaltered unsaved relationships array.'
	);

	// Restore jQuery.
	$.reset();

	assert.end();
} );

test( 'updateUnsavedRelationships (unchanged input, meta box not stored) ...', ( assert ) => {
	const testee = createTestee();

	const unsavedRelationships = [ 'metaBox' ];

	// Rewire internal data.
	RelationshipControl.__Rewire__( '_this', { unsavedRelationships } );

	// Turn method into stub.
	testee.findMetaBox = sinon.stub().returns( -1 );

	const event = {
		target: F.getRandomString()
	};

	const $metaBox = new jQueryObject();
	$metaBox.find.returns( new jQueryObject() );

	const $input = new jQueryObject();
	$input.closest.returns( $metaBox );
	$input.val.returns( 'stay' );

	$.withArgs( event.target ).returns( $input );

	assert.deepEqual(
		testee.updateUnsavedRelationships( event ),
		unsavedRelationships,
		'... SHOULD return the unaltered unsaved relationships array.'
	);

	// Restore jQuery.
	$.reset();

	assert.end();
} );

test( 'updateUnsavedRelationships (unchanged input, meta box stored) ...', ( assert ) => {
	const testee = createTestee();

	// Rewire internal data.
	RelationshipControl.__Rewire__( '_this', { unsavedRelationships: [ 'metaBox' ] } );

	// Turn method into stub.
	testee.findMetaBox = sinon.stub().returns( 0 );

	const event = {
		target: F.getRandomString()
	};

	const $metaBox = new jQueryObject();
	$metaBox.find.returns( new jQueryObject() );

	const $input = new jQueryObject();
	$input.closest.returns( $metaBox );
	$input.val.returns( 'stay' );

	$.withArgs( event.target ).returns( $input );

	assert.deepEqual(
		testee.updateUnsavedRelationships( event ),
		[],
		'... SHOULD return an empty array (which prior to calling updateUnsavedRelationships held one meta box object.'
	);

	// Restore jQuery.
	$.reset();

	assert.end();
} );

test( 'updateUnsavedRelationships (changed input, meta box not stored) ...', ( assert ) => {
	const testee = createTestee();

	// Turn method into stub.
	testee.findMetaBox = sinon.stub().returns( -1 );

	const event = {
		target: F.getRandomString()
	};

	const $metaBox = new jQueryObject();
	$metaBox.find.returns( new jQueryObject() );

	const $input = new jQueryObject();
	$input.closest.returns( $metaBox );

	$.withArgs( event.target ).returns( $input );

	assert.deepEqual(
		testee.updateUnsavedRelationships( event ),
		[ $metaBox ],
		'... SHOULD return the expected meta box object in the unsaved relationships array (which is empty by default).'
	);

	// Restore jQuery.
	$.reset();

	assert.end();
} );

test( 'findMetaBox (meta box not found) ...', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.findMetaBox( 'metaBox' ),
		-1,
		'... SHOULD return -1.'
	);

	assert.end();
} );

test( 'findMetaBox (meta box found) ...', ( assert ) => {
	const testee = createTestee();

	const $metaBox = 'metaBox';

	const unsavedRelationships = F.getRandomArray();

	const index = F.getRandomInteger( 0, unsavedRelationships.length );

	unsavedRelationships[ index ] = $metaBox;

	// Rewire internal data.
	RelationshipControl.__Rewire__( '_this', { unsavedRelationships } );

	assert.equal(
		testee.findMetaBox( $metaBox ),
		index,
		'... SHOULD return the expected index.'
	);

	assert.end();
} );

test( 'confirmUnsavedRelationships (no unsaved relationships) ...', ( assert ) => {
	const testee = createTestee();

	const event = {
		preventDefault: sinon.spy()
	};

	testee.confirmUnsavedRelationships( event );

	assert.equal(
		event.preventDefault.callCount,
		0,
		'... SHOULD NOT prevent publishing.'
	);

	assert.end();
} );

test( 'confirmUnsavedRelationships (unsaved relationships, user confirmed discarding) ...', ( assert ) => {
	const testee = createTestee();

	// Rewire internal data.
	RelationshipControl.__Rewire__( '_this', {
		settings: {
			L10n: {
				unsavedRelationships: ''
			}
		},
		unsavedRelationships: [ 'metaBox' ]
	} );

	const event = {
		preventDefault: sinon.spy()
	};

	// Make the "user" confirm discarding all unsaved relationships.
	window.confirm.returns( true );

	testee.confirmUnsavedRelationships( event );

	assert.equal(
		event.preventDefault.callCount,
		0,
		'... SHOULD NOT prevent publishing.'
	);

	// Reset window.
	window.confirm.reset();

	assert.end();
} );

test( 'confirmUnsavedRelationships (unsaved relationships, user canceled discarding) ...', ( assert ) => {
	const testee = createTestee();

	// Rewire internal data.
	RelationshipControl.__Rewire__( '_this', {
		settings: {
			L10n: {
				unsavedRelationships: ''
			}
		},
		unsavedRelationships: [ 'metaBox' ]
	} );

	const event = {
		preventDefault: sinon.spy()
	};

	// Make the "user" cancel discarding all unsaved relationships.
	window.confirm.returns( false );

	testee.confirmUnsavedRelationships( event );

	assert.equal(
		event.preventDefault.callCount,
		1,
		'... SHOULD prevent publishing.'
	);

	// Reset window.
	window.confirm.reset();

	assert.end();
} );

test( 'saveRelationship (nothing changed) ...', ( assert ) => {
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
		'... SHOULD NOT trigger any events.'
	);

	// Restore jQuery.
	$.reset();

	assert.end();
} );

test( 'saveRelationship (data changed) ...', ( assert ) => {
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

test( 'getEventName (search) ...', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.getEventName( 'search' ),
		'connectExistingPost',
		'... SHOULD return the expected result.'
	);

	assert.end();
} );

test( 'getEventName (new) ...', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.getEventName( 'new' ),
		'connectNewPost',
		'... SHOULD return the expected result.'
	);

	assert.end();
} );

test( 'getEventName (disconnect) ...', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.getEventName( 'disconnect' ),
		'disconnectPost',
		'... SHOULD return the expected result.'
	);

	assert.end();
} );

test( 'getEventName (unknown action) ...', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.getEventName(),
		'',
		'... SHOULD return an empty string.'
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

test( 'connectExistingPost (input checked) ...', ( assert ) => {
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
		'... SHOULD call sendRequest().'
	);

	// Manipulate data object for subsequent test.
	data.new_post_id = postID;

	assert.deepEqual(
		testee.sendRequest.firstCall.args[ 0 ],
		data,
		'... SHOULD pass the expected data to sendRequest().'
	);

	// Restore global scope.
	$.reset();

	assert.end();
} );

test( 'connectExistingPost (no input checked) ...', ( assert ) => {
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
		'... SHOULD NOT call sendRequest().'
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
