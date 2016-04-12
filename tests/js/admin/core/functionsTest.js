import test from "tape";
import * as Functions from "../../../../resources/js/admin/core/functions";

global.window = {};

test( 'getSettings returns the expected settings object', ( assert ) => {
	window = {};

	assert.deepEqual(
		Functions.getSettings( 'module' ),
		{},
		'getSettings SHOULD return an empty object if the requested settings could not be found.'
	);

	// Prepare "global" settings.
	global.window = {
		ModuleName: 'settings'
	};

	const ModuleName = () => 0;

	assert.equal(
		Functions.getSettings( ModuleName ),
		global.window.ModuleName,
		'getSettings SHOULD return the expected settings for a valid (module constructor) function.'
	);

	assert.equal(
		Functions.getSettings( 'ModuleName' ),
		global.window.ModuleName,
		'getSettings SHOULD return the expected settings for a valid module name.'
	);

	assert.equal(
		Functions.getSettings( new ModuleName() ),
		global.window.ModuleName,
		'getSettings SHOULD return the expected settings for a valid module instance.'
	);

	// Prepare "global" settings.
	global.window.mlpModuleNameSettings = 'other-settings';

	assert.equal(
		Functions.getSettings( 'ModuleName' ),
		global.window.mlpModuleNameSettings,
		'getSettings SHOULD return the expected settings for a valid module name.'
	);

	assert.end();
} );
