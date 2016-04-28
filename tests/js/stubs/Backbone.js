import sinon from "sinon";

const Backbone = {
	Events: {},
	history: {
		start: sinon.spy()
	},
	History: {
		started: false
	},
	Model: () => {
	},
	Router: () => {
	},
	View: () => {
	}
};
Backbone.Model.prototype.fetch = sinon.spy();
Backbone.Model.prototype.get = sinon.stub();
Backbone.Router.prototype.route = sinon.spy();
Backbone.View.prototype.listenTo = sinon.spy();

export default Backbone;
