import sinon from "sinon";
let globalStub = {};
global.document = globalStub.document = {};
global.window = globalStub.window = {};
global.Backbone = globalStub.Backbone = {};

Backbone.View = Backbone.Model = Backbone.Router = class {
};
Backbone.View.prototype.listenTo = sinon.spy();

global._ = globalStub._ = sinon.spy();

const jQueryMethods = {
	val: sinon.stub()
};
let jQuery = sinon.stub();
jQuery.returns(jQueryMethods);

for ( let method in jQueryMethods ) {
	if ( jQueryMethods.hasOwnProperty( method ) ) {
		jQuery[ method ] = jQueryMethods[ method ];
	}
}

window.$
	= window.jQuery
	= globalStub.$
	= globalStub.jQuery
	= jQuery;

export default globalStub;