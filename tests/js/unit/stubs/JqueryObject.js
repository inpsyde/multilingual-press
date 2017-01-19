/* eslint-disable no-invalid-this */

import sinon from 'sinon';
import * as _ from 'lodash';

/**
 * Creates a jQuery object for testing.
 * @param {Object} [customMembers={}] - Optional. Custom members of the jQuery object. Defaults to empty object.
 * @constructor
 */
export default function JqueryObject( customMembers = {} ) {
	const members = _.extend( {
		_elements: [],

		addClass: sinon.spy(),
		append: sinon.spy(),
		attr: sinon.stub(),
		before: sinon.spy(),
		closest: sinon.stub().returns( this ),
		css: sinon.spy(),
		data: sinon.stub(),
		each: ( c ) => {
			this._elements.forEach( ( e, i ) => {
				c( i, e );
			} );
		},
		filter: sinon.stub(),
		find: sinon.stub().returns( this ),
		first: sinon.stub().returns( this ),
		hide: sinon.stub(),
		html: sinon.stub(),
		is: sinon.stub(),
		not: sinon.stub(),
		on: sinon.stub(),
		prop: sinon.stub(),
		removeAttr: sinon.spy(),
		removeClass: sinon.spy(),
		text: sinon.stub().returns( '' ),
		toggle: sinon.spy(),
		val: sinon.stub()
	}, customMembers );

	Object.keys( members ).forEach( ( key ) => {
		this[ key ] = members[ key ];
	} );

	this.length = this._elements.length;
}
