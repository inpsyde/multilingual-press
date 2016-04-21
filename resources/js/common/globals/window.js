let localWindow;

if ( 'undefined' === typeof window ) {
	localWindow = {};
} else {
	localWindow = window;
}

export default localWindow;