import sinon from 'sinon';
import * as F from '../functions';

const _ = sinon.stub();
_.extend = sinon.stub();
_.template = sinon.stub().returns( F.returnArg );

_._restore = () => {
	_.template.reset();
	_.reset();
};

export default _;
