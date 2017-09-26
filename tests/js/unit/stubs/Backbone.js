import sinon from 'sinon';
import JqueryObject from './JqueryObject';

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
	Model() {
	},
	Router() {
	},
	View( { $el: $el = new JqueryObject(), model: model = new Backbone.Model() } = {} ) {
		this.$el = $el;
		this.model = model;
	},
	_restore() {
		this.Events.on.reset();
		this.Events.trigger.reset();
		this.history.start.reset();
		this.History.started = false;
	}
};

Backbone.Model.prototype.fetch = sinon.spy();
Backbone.Model.prototype.save = sinon.spy();
Backbone.Model.prototype.get = sinon.stub();

Backbone.Router.prototype.route = sinon.spy();

Backbone.View.prototype.listenTo = sinon.spy();

export default Backbone;
