import sinon from "sinon";
let globalStub = {};
global.document = globalStub.document = {};
global.window = globalStub.window = {};
global.Backbone = globalStub.Backbone = {};

Backbone.View = Backbone.Model = Backbone.Router = class {
};
Backbone.View.prototype.listenTo = sinon.spy();

global._ = globalStub._ = sinon.spy();

let jQuery = function( selector ) {
	selector = selector || false;
	return {
		val: sinon.spy()
	}
};
global.$
	= global.jQuery
	= window.$
	= window.jQuery
	= globalStub.$
	= globalStub.jQuery
	= jQuery;

export default globalStub;