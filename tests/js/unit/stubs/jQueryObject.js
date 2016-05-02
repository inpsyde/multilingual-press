/* eslint-disable no-invalid-this */

import * as _ from "lodash";
import sinon from "sinon";

export default function jQueryObject( customMembers = {} ) {
	const members = _.extend( {
		_elements: [],

		attr: sinon.stub(),
		data: sinon.stub(),
		each: ( c ) => {
			for ( let i = 0; i < this._elements.length; i++ ) {
				c( i, this._elements[ i ] );
			}
		},
		find: sinon.stub(),
		is: sinon.stub(),
		on: sinon.stub(),
		text: sinon.stub(),
		toggle: sinon.spy(),
		val: sinon.stub()
	}, customMembers );
	Object.keys( members ).forEach( ( key ) => {
		this[ key ] = members[ key ];
	} );
	this.length = this._elements.length;
}
