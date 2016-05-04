/* eslint-disable no-invalid-this */

import sinon from "sinon";
import * as _ from "lodash";

export default function jQueryObject( customMembers = {} ) {
	const members = _.extend( {
		_elements: [],

		addClass: sinon.spy(),
		append: sinon.spy(),
		attr: sinon.stub(),
		before: sinon.spy(),
		closest: sinon.stub(),
		css: sinon.spy(),
		data: sinon.stub(),
		each: ( c ) => {
			for ( let i = 0; i < this._elements.length; i++ ) {
				c( i, this._elements[ i ] );
			}
		},
		filter: sinon.stub(),
		find: sinon.stub(),
		first: sinon.stub(),
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
