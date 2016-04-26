import "../../stubs/global";
import test from "tape";
import sinon from "sinon";
import * as F from "../../functions";
import jQueryStub from "../../stubs/jQueryObject";
import UserBackEndLanguage from "../../../../resources/js/admin/user-settings/UserBackEndLanguage";

test( 'settings behaves as expected', ( assert ) => {
	const settings = F.getRandomString();

	const testee = new UserBackEndLanguage( { settings } );

	assert.equal(
		testee.settings,
		settings,
		'settings SHOULD have the expected value.'
	);

	assert.end();
} );

test( 'updateSiteLanguage behaves as expected', ( assert ) => {
	const options = {
		settings: {
			locale: F.getRandomString()
		}
	};

	const testee = new UserBackEndLanguage( options );

	// Assign fake jQuery object.
	testee.$el = new jQueryStub( {
		val: sinon.spy()
	} );

	testee.updateSiteLanguage();

	assert.equal(
		testee.$el.val.callCount,
		1,
		'updateSiteLanguage SHOULD set a value.'
	);

	assert.equal(
		testee.$el.val.calledWith( options.settings.locale ),
		true,
		'updateSiteLanguage SHOULD set the expected value.'
	);

	assert.end();
} );
