import sinon from "sinon";
global.document = sinon.spy();
global.window = {};
global.Backbone = sinon.spy();

Backbone.View = Backbone.Model = Backbone.Router = class {
}
Backbone.View.prototype.listenTo = sinon.spy();

global._ = sinon.spy();

let jQuery = function( selector ) {
	return {
		val: sinon.spy()
	}
}
global.$ = global.jQuery = window.$ = window.jQuery = jQuery;
