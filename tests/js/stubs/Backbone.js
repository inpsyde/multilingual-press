import sinon from "sinon";
import jQueryObject from "./jQueryObject";

const Backbone = {
	Events: {},
	history: {
		start: sinon.spy()
	},
	History: {
		started: false
	},
	Model: function() {
	},
	Router: function() {
	},
	View: function( options = {} ) {
		this.$el = options.$el || new jQueryObject();
	}
};
Backbone.Model.prototype.fetch = sinon.spy();
Backbone.Model.prototype.get = sinon.stub();
Backbone.Router.prototype.route = sinon.spy();
Backbone.View.prototype.listenTo = sinon.spy();

export default Backbone;
