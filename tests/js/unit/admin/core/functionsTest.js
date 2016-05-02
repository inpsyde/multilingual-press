import test from "tape";
import * as F from "../../functions";
import * as Functions from "../../../../../resources/js/admin/core/functions";

const window = global.window = {};

test( 'getSettings returns an empty object if the requested settings could not be found', ( assert ) => {
	assert.deepEqual(
		Functions.getSettings( 'module' ),
		{},
		'getSettings SHOULD return an empty object if the requested settings could not be found.'
	);

	assert.end();
} );

test( 'getSettings returns the expected settings object', ( assert ) => {
	// Prepare "global" settings.
	window.ModuleName = F.getRandomString();

	const ModuleName = () => 0;

	assert.equal(
		Functions.getSettings( ModuleName ),
		window.ModuleName,
		'getSettings SHOULD return the expected settings for a valid (module constructor) function.'
	);

	// Restore window.
	delete window.ModuleName;

	assert.end();
} );

test( 'getSettings returns the expected settings object', ( assert ) => {
	// Prepare "global" settings.
	window.ModuleName = F.getRandomString();

	assert.equal(
		Functions.getSettings( 'ModuleName' ),
		window.ModuleName,
		'getSettings SHOULD return the expected settings for a valid module name.'
	);

	// Restore window.
	delete window.ModuleName;

	assert.end();
} );

test( 'getSettings returns the expected settings object', ( assert ) => {
	// Prepare "global" settings.
	window.ModuleName = F.getRandomString();

	const ModuleName = () => 0;

	assert.equal(
		Functions.getSettings( new ModuleName() ),
		window.ModuleName,
		'getSettings SHOULD return the expected settings for a valid module instance.'
	);

	// Restore window.
	delete window.ModuleName;

	assert.end();
} );

test( 'getSettings returns the expected settings object', ( assert ) => {
	// Prepare "global" settings.
	window.ModuleName = F.getRandomString();
	window.mlpModuleNameSettings = F.getRandomString();

	assert.equal(
		Functions.getSettings( 'ModuleName' ),
		window.mlpModuleNameSettings,
		'getSettings SHOULD return the expected settings for a valid module name.'
	);

	// Restore window.
	delete window.ModuleName;
	delete window.mlpModuleNameSettings;

	assert.end();
} );
