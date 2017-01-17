import globalStub from '../../stubs/global';
import test from 'tape';
import sinon from 'sinon';
import * as _ from 'lodash';
import * as F from '../../functions';
import JqueryObject from '../../stubs/JqueryObject';
import AddNewSite from '../../../../../resources/js/admin/network/AddNewSite';

const { $ } = global;

/**
 * Returns a new instance of the class under test.
 * @param {Object} [options] - Optional. The constructor options.
 * @returns {AddNewSite} The instance of the class under test.
 */
const createTestee = ( options ) => {
	// Rewire internal data.
	AddNewSite.__Rewire__( '_this', {} );

	return new AddNewSite( _.extend( { settings: {} }, options ) );
};

test( 'constructor ...', ( assert ) => {
	createTestee();

	assert.equal(
		global._.template.callCount,
		0,
		'... SHOULD NOT render any markup.'
	);

	assert.end();
} );

test( 'adaptLanguage (language found) ...', ( assert ) => {
	const $language = new JqueryObject();
	$language.find.returns( new JqueryObject( { _elements: [ 'language' ] } ) );

	$.withArgs( '#mlp-site-language' ).returns( $language );

	const testee = createTestee();

	const language = F.getRandomString();

	// Make method return a random language.
	testee.getLanguage = () => language;

	testee.adaptLanguage( { target: 'target' } );

	assert.equal(
		$language.val.calledWith( language ),
		true,
		'... SHOULD set the expected language.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );

test( 'adaptLanguage (language not found) ...', ( assert ) => {
	const $language = new JqueryObject();
	$language.find.returns( new JqueryObject() );

	$.withArgs( '#mlp-site-language' ).returns( $language );

	const testee = createTestee();

	// Make method return an empty string.
	testee.getLanguage = F.returnEmptyString;

	testee.adaptLanguage( { target: 'target' } );

	assert.equal(
		$language.val.callCount,
		0,
		'... SHOULD NOT set the language.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );

test( 'getLanguage (English, United States) ...', ( assert ) => {
	const testee = createTestee();

	assert.equal(
		testee.getLanguage( new JqueryObject() ),
		'en-US',
		'... SHOULD return the locale for English (United States).'
	);

	assert.end();
} );

test( 'getLanguage (German, Germany) ...', ( assert ) => {
	const testee = createTestee();

	const $select = new JqueryObject();
	$select.val.returns( 'de_DE' );

	assert.equal(
		testee.getLanguage( $select ),
		'de-DE',
		'... SHOULD return the locale for German (Germany).'
	);

	assert.end();
} );

test( 'getLanguage (Klingon) ...', ( assert ) => {
	const testee = createTestee();

	const language = 'tlh';

	const $select = new JqueryObject();
	$select.val.returns( language );

	assert.equal(
		testee.getLanguage( $select ),
		language,
		'... SHOULD return the (unaltered) locale for Klingon.'
	);

	assert.end();
} );

test( 'togglePluginsRow ...', ( assert ) => {
	const testee = createTestee();

	const $pluginsRow = {
		toggle: sinon.spy()
	};

	AddNewSite.__Rewire__( '_this', { $pluginsRow } );

	const siteID = F.getRandomBool() ? F.getRandomInteger( 1 ) : '';

	const $siteID = new JqueryObject();
	$siteID.val.returns( siteID );

	$.returns( $siteID );

	testee.togglePluginsRow( { target: 'target' } );

	assert.equal(
		$pluginsRow.toggle.calledWith( 0 < siteID ),
		true,
		'... SHOULD toggle the plugins row according to the given site ID.'
	);

	// Restore global scope.
	globalStub.restore();

	assert.end();
} );
