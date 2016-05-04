import sinon from "sinon";
import jQueryObject from "./jQueryObject";

const Backbone = {
	Events: {
		on: sinon.spy(),
		trigger: sinon.spy()
	},
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
		this.model = options.model || new Backbone.Model();
	}
};

Backbone._restore = () => {
	Backbone.Events.on.reset();
	Backbone.Events.trigger.reset();
	Backbone.history.start.reset();
	Backbone.History.started = false;
};

Backbone.Model.prototype.fetch = sinon.spy();
Backbone.Model.prototype.get = sinon.stub();

Backbone.Router.prototype.route = sinon.spy();

Backbone.View.prototype.listenTo = sinon.spy();

export default Backbone;
