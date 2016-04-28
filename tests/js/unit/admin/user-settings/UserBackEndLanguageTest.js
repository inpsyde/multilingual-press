import "../../stubs/global";
import test from "tape";
import * as F from "../../functions";
import jQueryObject from "../../stubs/jQueryObject";
import UserBackEndLanguage from "../../../../../resources/js/admin/user-settings/UserBackEndLanguage";

test( 'settings ...', ( assert ) => {
	const options = {
		settings: F.getRandomString()
	};

	const testee = new UserBackEndLanguage( options );

	assert.equal(
		testee.settings,
		options.settings,
		'... SHOULD have the expected value.'
	);

	assert.end();
} );

test( 'updateSiteLanguage ...', ( assert ) => {
	const options = {
		settings: {
			locale: F.getRandomString()
		}
	};

	const testee = new UserBackEndLanguage( options );

	// Assign fake jQuery object.
	testee.$el = new jQueryObject();

	testee.updateSiteLanguage();

	assert.equal(
		testee.$el.val.callCount,
		1,
		'... SHOULD set a value.'
	);

	assert.equal(
		testee.$el.val.calledWith( options.settings.locale ),
		true,
		'... SHOULD set the expected value.'
	);

	assert.end();
} );
