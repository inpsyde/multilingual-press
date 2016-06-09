import globalStub from "../../stubs/global";
import test from "tape";
import sinon from "sinon";
import * as _ from "lodash";
import * as F from "../../functions";
import jQueryObject from "../../stubs/jQueryObject";
import CopyPost from "../../../../../resources/js/admin/post-translation/CopyPost";

const { $, Backbone, window } = global;

/**
 * Returns a new instance of the class under test.
 * @param {Object} [options] - Optional. The constructor options.
 * @returns {CopyPost} The instance of the class under test.
 */
const createTestee = ( options ) => new CopyPost( _.extend( { settings: {} }, options ) );

test( 'constructor ...', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.listenTo.calledWith( testee.model, 'change:data', testee.updatePostData ),
		true,
		'... SHOULD attach the expected event listener.'
	);

	assert.end();
} );

test( 'content (element missing) ...', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.content,
		'',
		'... SHOULD be empty.'
	);

	assert.end();
} );

test( 'content (element present) ...', ( assert ) => {
	const content = F.getRandomString();

	const $content = new jQueryObject();
	$content.val.returns( content );

	$.withArgs( '#content' ).returns( $content );

	const testee = createTestee();

	assert.equal(
		testee.content,
		content,
		'... SHOULD have the value of the according element.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );

test( 'excerpt (element missing) ...', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.excerpt,
		'',
		'... SHOULD be empty.'
	);

	assert.end();
} );

test( 'excerpt (element present) ...', ( assert ) => {
	const excerpt = F.getRandomString();

	const $excerpt = new jQueryObject();
	$excerpt.val.returns( excerpt );

	$.withArgs( '#excerpt' ).returns( $excerpt );

	const testee = createTestee();

	assert.equal(
		testee.excerpt,
		excerpt,
		'... SHOULD have the value of the according element.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );

test( 'postID (element missing) ...', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.postID,
		0,
		'... SHOULD be 0.'
	);

	assert.end();
} );

test( 'postID (element present) ...', ( assert ) => {
	const postID = F.getRandomInteger( 1 );

	const $postID = new jQueryObject();
	$postID.val.returns( postID );

	$.withArgs( '#post_ID' ).returns( $postID );

	const testee = createTestee();

	assert.equal(
		testee.postID,
		postID,
		'... SHOULD have the value of the according element.'
	);

	assert.end();
} );

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

test( 'slug (element missing) ...', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.slug,
		'',
		'... SHOULD be empty.'
	);

	assert.end();
} );

test( 'slug (element present) ...', ( assert ) => {
	const slug = F.getRandomString();

	const $slug = new jQueryObject();
	$slug.text.returns( slug );

	$.withArgs( '#editable-post-name-full' ).returns( $slug );

	const testee = createTestee();

	assert.equal(
		testee.slug,
		slug,
		'... SHOULD have the value of the according element.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );

test( 'tinyMCEContent (TinyMCE undefined) ...', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.tinyMCEContent,
		'',
		'... SHOULD be empty.'
	);

	assert.end();
} );

test( 'tinyMCEContent (TinyMCE missing) ...', ( assert ) => {
	// Prepare tinyMCE.
	window.tinyMCE = {
		get: F.returnNull
	};

	const testee = createTestee();

	assert.equal(
		testee.tinyMCEContent,
		'',
		'... SHOULD be empty.'
	);

	// Restore window.
	delete window.tinyMCE;

	assert.end();
} );

test( 'tinyMCEContent (TinyMCE present) ...', ( assert ) => {
	const tinyMCEContent = F.getRandomString();

	// Prepare tinyMCE.
	window.tinyMCE = {
		get: sinon.stub().withArgs( 'content' ).returns( {
			getContent: () => tinyMCEContent
		} )
	};

	const testee = createTestee();

	assert.equal(
		testee.tinyMCEContent,
		tinyMCEContent,
		'... SHOULD have the expected value.'
	);

	// Restore window.
	delete window.tinyMCE;

	assert.end();
} );

test( 'title (element missing) ...', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.title,
		'',
		'... SHOULD be empty.'
	);

	assert.end();
} );

test( 'title (element present) ...', ( assert ) => {
	const title = F.getRandomString();

	const $title = new jQueryObject();
	$title.val.returns( title );

	$.withArgs( '#title' ).returns( $title );

	const testee = createTestee();

	assert.equal(
		testee.title,
		title,
		'... SHOULD have the value of the according element.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );

test.only( 'copyPostData ...', ( assert ) => {
	const tinyMCEContent = F.getRandomString();

	// Prepare tinyMCE.
	window.tinyMCE = {
		get: sinon.stub().withArgs( 'content' ).returns( {
			getContent: () => tinyMCEContent
		} )
	};

	Backbone.Events.trigger.reset();

	const EventManager = Backbone.Events;

	const model = new Backbone.Model();

	const options = {
		EventManager,
		model,
		settings: {
			action: F.getRandomString(),
			siteID: F.getRandomInteger( 1 )
		}
	};

	const content = F.getRandomString();

	const $content = new jQueryObject();
	$content.val.returns( content );

	const excerpt = F.getRandomString();

	const $excerpt = new jQueryObject();
	$excerpt.val.returns( excerpt );

	const slug = F.getRandomString();

	const $slug = new jQueryObject();
	$slug.text.returns( slug );

	const title = F.getRandomString();

	const $title = new jQueryObject();
	$title.val.returns( title );

	const $copiedPostFlag = new jQueryObject();

	const remoteSiteID = F.getRandomInteger( 1 );

	$
		.withArgs( '#content' ).returns( $content )
		.withArgs( '#excerpt' ).returns( $excerpt )
		.withArgs( '#editable-post-name-full' ).returns( $slug )
		.withArgs( '#title' ).returns( $title )
		.withArgs( '#mlp-translation-data-' + remoteSiteID + '-copied-post' ).returns( $copiedPostFlag );

	const testee = createTestee( options );

	// Turn method into stub.
	testee.getRemoteSiteID = sinon.stub().returns( remoteSiteID );

	// Turn method into spy.
	testee.fadeOutMetaBox = sinon.spy();

	const event = {
		preventDefault: sinon.spy(),
		target: 'target'
	};

	// Make _.extend() return the second argument (which is the default data).
	global._.extend = sinon.stub().returnsArg( 1 );

	testee.copyPostData( event );

	assert.equal(
		event.preventDefault.callCount,
		1,
		'... SHOULD prevent the event default.'
	);

	assert.equal(
		$copiedPostFlag.val.calledWith( 1 ),
		true,
		'... SHOULD set the copied-post flag.'
	);

	assert.equal(
		EventManager.trigger.calledWith( 'CopyPost:copyPostData', {}, options.settings.siteID, 0, remoteSiteID ),
		true,
		'... SHOULD trigger the expected event.'
	);

	const data = {
		action: options.settings.action,
		current_post_id: 0,
		remote_site_id: remoteSiteID,
		title,
		slug,
		tinyMCEContent,
		content,
		excerpt
		};

	assert.deepEqual(
		model.save.calledWith( data, { data, processData: true } ),
		true,
		'... SHOULD fetch new data.'
	);

	// Restore lodash.
	global._ = sinon.stub();

	// Restore window.
	delete window.tinyMCE;

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );

test( 'getRemoteSiteID (no site ID) ...', ( assert ) => {
	const testee = createTestee();

	const $button = new jQueryObject();

	assert.equal(
		testee.getRemoteSiteID( $button ),
		0,
		'... SHOULD return 0.'
	);

	assert.end();
} );

test( 'getRemoteSiteID (site ID specified) ...', ( assert ) => {
	const testee = createTestee();

	const siteID = F.getRandomInteger();

	const $button = new jQueryObject();
	$button.data.returns( siteID );

	assert.equal(
		testee.getRemoteSiteID( $button ),
		siteID,
		'... SHOULD return the site ID.'
	);

	assert.end();
} );

test( 'fadeOutMetaBox ...', ( assert ) => {
	const testee = createTestee();

	const remoteSiteID = F.getRandomInteger( 1 );

	const $metaBox = new jQueryObject();

	$.withArgs( '#inpsyde_multilingual_' + remoteSiteID ).returns( $metaBox );

	testee.fadeOutMetaBox( remoteSiteID );

	assert.equal(
		$metaBox.css.callCount,
		1,
		'... SHOULD alter the CSS.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );

test( 'updatePostData (unsuccessful AJAX request) ...', ( assert ) => {
	Backbone.Events.trigger.reset();

	const model = new Backbone.Model();
	model.get.returns( false );

	const options = {
		EventManager: Backbone.Events,
		model
	};

	const testee = createTestee( options );

	// Turn method into spy.
	testee.setTinyMCEContent = sinon.spy();

	// Turn method into spy.
	testee.fadeInMetaBox = sinon.spy();

	// Create a generic jQuery result.
	const $jQueryObject = new jQueryObject();

	$.returns( $jQueryObject );

	assert.equal(
		testee.updatePostData(),
		false,
		'... SHOULD return false.'
	);

	assert.equal(
		$jQueryObject.val.callCount,
		0,
		'... SHOULD NOT touch any elements.'
	);

	assert.equal(
		testee.setTinyMCEContent.callCount,
		0,
		'... SHOULD NOT change any tinyMCE content.'
	);

	assert.equal(
		options.EventManager.trigger.callCount,
		0,
		'... SHOULD NOT trigger any events.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );

test( 'updatePostData (successful AJAX request) ...', ( assert ) => {
	Backbone.Events.trigger.reset();

	const data = {
		content: F.getRandomString(),
		excerpt: F.getRandomString(),
		siteID: F.getRandomInteger( 1 ),
		slug: F.getRandomString(),
		tinyMCEContent: F.getRandomString(),
		title: F.getRandomString()
	};

	const model = new Backbone.Model();
	model.get
		.withArgs( 'success' ).returns( true )
		.withArgs( 'data' ).returns( data );

	const options = {
		EventManager: Backbone.Events,
		model
	};

	const testee = createTestee( options );

	// Turn method into spy.
	testee.setTinyMCEContent = sinon.spy();

	// Turn method into spy.
	testee.fadeInMetaBox = sinon.spy();

	const prefix = 'mlp-translation-data-' + data.siteID + '-';

	const $title = new jQueryObject();

	const $slug = new jQueryObject();

	const $content = new jQueryObject();

	const $excerpt = new jQueryObject();

	$
		.withArgs( '#' + prefix + 'title' ).returns( $title )
		.withArgs( '#' + prefix + 'name' ).returns( $slug )
		.withArgs( '#' + prefix + 'content' ).returns( $content )
		.withArgs( '#' + prefix + 'excerpt' ).returns( $excerpt );

	assert.equal(
		testee.updatePostData(),
		true,
		'... SHOULD return true.'
	);

	assert.equal(
		$title.val.calledWith( data.title ),
		true,
		'... SHOULD set the expected title.'
	);

	assert.equal(
		$slug.val.calledWith( data.slug ),
		true,
		'... SHOULD set the expected slug.'
	);

	assert.equal(
		testee.setTinyMCEContent.calledWith( prefix + 'content', data.tinyMCEContent ),
		true,
		'... SHOULD set the tinyMCE content to the expected value.'
	);

	assert.equal(
		$content.val.calledWith( data.content ),
		true,
		'... SHOULD set the expected content.'
	);

	assert.equal(
		$excerpt.val.calledWith( data.excerpt ),
		true,
		'... SHOULD set the expected excerpt.'
	);

	assert.equal(
		options.EventManager.trigger.calledWith( 'CopyPost:updatePostData', data ),
		true,
		'... SHOULD trigger the expected event.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );

test( 'setTinyMCEContent (no tinyMCE) ...', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.setTinyMCEContent(),
		false,
		'... SHOULD return false.'
	);

	assert.end();
} );

test( 'setTinyMCEContent (requested tinyMCE not available) ...', ( assert ) => {
	const testee = createTestee();

	// Mock tinyMCE.
	window.tinyMCE = {
		get: F.returnFalse
	};

	assert.equal(
		testee.setTinyMCEContent(),
		false,
		'... SHOULD return false.'
	);

	// Restore window.
	delete window.tinyMCE;

	assert.end();
} );

test( 'setTinyMCEContent (requested tinyMCE available) ...', ( assert ) => {
	const testee = createTestee();

	const editor = {
		setContent: sinon.spy()
	};

	// Mock tinyMCE.
	window.tinyMCE = {
		get: () => editor
	};

	const content = F.getRandomString();

	assert.equal(
		testee.setTinyMCEContent( 'editorID', content ),
		true,
		'... SHOULD return true.'
	);

	assert.equal(
		editor.setContent.calledWith( content ),
		true,
		'... SHOULD set the expected tinyMCE content.'
	);

	// Restore window.
	delete window.tinyMCE;

	assert.end();
} );

test( 'fadeInMetaBox ...', ( assert ) => {
	const testee = createTestee();

	const remoteSiteID = F.getRandomInteger( 1 );

	const $metaBox = new jQueryObject();

	$.withArgs( '#inpsyde_multilingual_' + remoteSiteID ).returns( $metaBox );

	testee.fadeInMetaBox( remoteSiteID );

	assert.equal(
		$metaBox.css.callCount,
		1,
		'... SHOULD alter the CSS.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );
