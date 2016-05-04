import globalStub from "../../stubs/global";
import test from "tape";
import sinon from "sinon";
import * as _ from "lodash";
import * as F from "../../functions";
import Backbone from "../../stubs/Backbone";
import jQueryObject from "../../stubs/jQueryObject";
import NavMenus from "../../../../../resources/js/admin/nav-menus/NavMenus";

const { $ } = global;

/**
 * Returns a new instance of the class under test.
 * @param {Object} [options] - Optional. The constructor options.
 * @returns {NavMenus} The instance of the class under test.
 */
const createTestee = ( options ) => {
	// Rewire internal data.
	NavMenus.__Rewire__( '_this', {} );

	return new NavMenus( _.extend( { settings: {} }, options ) );
};

test( 'constructor ...', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.listenTo.calledWith( testee.model, 'change', testee.render ),
		true,
		'... SHOULD attach the expected event listener.'
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

test( 'sendRequest ...', ( assert ) => {
	const menu = F.getRandomString();

	const $menu = new jQueryObject();
	$menu.val.returns( menu );

	$.withArgs( '#menu' ).returns( $menu );

	const $el = new jQueryObject();
	$el.find.returns( new jQueryObject() );

	const model = new Backbone.Model();

	const options = {
		$el,
		model,
		settings: {
			action: F.getRandomString(),
			nonce: F.getRandomString(),
			nonceName: F.getRandomString()
		}
	};

	const testee = createTestee( options );

	const siteIDs = F.getRandomArray();

	// Make method return a random array.
	testee.getSiteIDs = () => siteIDs;

	const event = {
		preventDefault: sinon.spy()
	};

	testee.sendRequest( event );

	const data = {
		data: {
			action: options.settings.action,
			menu,
			mlp_sites: siteIDs
		},
		processData: true
	};
	data.data[ options.settings.nonceName ] = options.settings.nonce;

	assert.equal(
		event.preventDefault.callCount,
		1,
		'... SHOULD prevent the event default.'
	);

	assert.equal(
		model.fetch.calledWith( data ),
		true,
		'... SHOULD fetch new data.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );

test( 'getSiteIDs (no checked languages) ...', ( assert ) => {
	const $languages = new jQueryObject();
	$languages.filter.returnsThis();

	const $el = new jQueryObject();
	$el.find.returns( $languages );

	const testee = createTestee( { $el } );

	assert.deepEqual(
		testee.getSiteIDs(),
		[],
		'... SHOULD return an empty array (which is the default).'
	);

	assert.end();
} );

test( 'getSiteIDs (checked languages) ...', ( assert ) => {
	const testee = createTestee();

	const _elements = F.getRandomArray( 1 );

	const $languages = new jQueryObject( { _elements } );
	$languages.filter.returnsThis();

	NavMenus.__Rewire__( '_this', {
		$languages
	} );

	assert.equal(
		testee.getSiteIDs().length,
		_elements.length,
		'... SHOULD return an array with the expected length.'
	);

	assert.end();
} );

test( 'render (unsuccessful AJAX request) ...', ( assert ) => {
	const $el = new jQueryObject();
	$el.find.returns( new jQueryObject() );

	const model = new Backbone.Model();
	model.get.returns( false );

	const options = {
		$el,
		model
	};

	const testee = createTestee( options );

	assert.equal(
		testee.render(),
		false,
		'... SHOULD return false.'
	);

	assert.equal(
		model.get.calledWith( 'data' ),
		false,
		'... SHOULD NOT fetch (and render) any data.'
	);

	assert.end();
} );

test( 'render (successful AJAX request) ...', ( assert ) => {
	const $menuToEdit = new jQueryObject();

	$.withArgs( '#menu-to-edit' ).returns( $menuToEdit );

	const $el = new jQueryObject();
	$el.find.returns( new jQueryObject() );

	const data = F.getRandomString();

	const model = new Backbone.Model();
	model.get
		.withArgs( 'success' ).returns( true )
		.withArgs( 'data' ).returns( data );

	const options = {
		$el,
		model
	};

	const testee = createTestee( options );

	assert.equal(
		testee.render(),
		true,
		'... SHOULD return true.'
	);

	assert.equal(
		$menuToEdit.append.calledWith( data ),
		true,
		'... SHOULD render the expected data.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );
