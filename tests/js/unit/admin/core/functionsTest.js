import test from 'tape';
import * as F from '../../functions';
import * as Functions from '../../../../../resources/js/common/functions';

const window = {};

global.window = window;

test( 'getSettings (settings not found) ...', ( assert ) => {
	assert.deepEqual(
		Functions.getSettings( 'module' ),
		{},
		'... SHOULD return an empty object.'
	);

	assert.end();
} );

test( 'getSettings (constructor function) ...', ( assert ) => {
	// Prepare "global" settings.
	window.ModuleName = F.getRandomString();

	const ModuleName = () => 0;

	assert.equal(
		Functions.getSettings( ModuleName ),
		window.ModuleName,
		'... SHOULD return the expected settings object.'
	);

	// Restore window.
	delete window.ModuleName;

	assert.end();
} );

test( 'getSettings (module name) ...', ( assert ) => {
	// Prepare "global" settings.
	window.ModuleName = F.getRandomString();

	assert.equal(
		Functions.getSettings( 'ModuleName' ),
		window.ModuleName,
		'... SHOULD return the expected settings object.'
	);

	// Restore window.
	delete window.ModuleName;

	assert.end();
} );

test( 'getSettings (module instance) ...', ( assert ) => {
	// Prepare "global" settings.
	window.ModuleName = F.getRandomString();

	const ModuleName = () => 0;

	assert.equal(
		Functions.getSettings( new ModuleName() ),
		window.ModuleName,
		'... SHOULD return the expected settings object.'
	);

	// Restore window.
	delete window.ModuleName;

	assert.end();
} );

test( 'getSettings (module name, settings variable pre- and postfixed) ...', ( assert ) => {
	// Prepare "global" settings.
	window.mlpModuleNameSettings = F.getRandomString();
	window.ModuleName = F.getRandomString();

	assert.equal(
		Functions.getSettings( 'ModuleName' ),
		window.mlpModuleNameSettings,
		'... SHOULD return the expected settings object.'
	);

	// Restore window.
	delete window.mlpModuleNameSettings;
	delete window.ModuleName;

	assert.end();
} );
