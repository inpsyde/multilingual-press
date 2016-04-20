let localBackbone;

if ( 'undefined' === typeof Backbone ) {
	localBackbone = {
		Router: class Router {
			constructor() {
			}
		}
	};
} else {
	localBackbone = Backbone;
}

export default localBackbone;