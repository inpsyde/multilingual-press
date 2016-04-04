import test from 'tape';

import MultilingualPress from '../../../resources/js/frontend/MultilingualPress';

global.window = {
	location: {
		href: ''
	}
};

test( 'setLocation redirects the user to the given URL', function( assert ) {
	assert.equal(
		typeof MultilingualPress.setLocation,
		'function',
		'setLocation should be a function.'
	);

	const url = 'url';

	MultilingualPress.setLocation( url );

	assert.equal(
		global.window.location.href,
		url,
		'setLocation should redirect the user to the given URL.'
	);

	assert.end();
} );
