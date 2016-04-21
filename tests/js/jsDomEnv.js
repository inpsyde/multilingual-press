import jsdom from "jsdom";
global.document = jsdom.jsdom("<html><head></head><body>hello world</body></html>");
global.window = document.defaultView;
