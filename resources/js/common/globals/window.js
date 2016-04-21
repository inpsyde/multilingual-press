let localWindow;

if ( 'undefined' === typeof window ) {
	localWindow = {};
	// global.document = {
	// 	createElement:function(){}
	// };
} else {
	localWindow = window;
}

export default localWindow;