import * as F from './common/functions';
import * as Util from './common/utils';
import Redirector from './redirect/Redirector';

// Initialize the Redirector module.
( new Redirector( F.getSettings( 'Redirector' ), Util ) ).initialize();
