/**
 * Created by EC_l on 16.01.14.
 */
var page = require('webpage').create();
var url = require('system').args[3];
var headers = '';
page.viewportSize = {
	width: require('system').args[4],
	height: require('system').args[5]
};
page.customHeaders = {
	"Referer" : require('system').args[2]
};
page.onResourceReceived = function(response) {
	for (var key in response.headers) {
		headers = headers + response.headers[key].name + ": " + response.headers[key].value + "\n";
	}
	headers = headers + "\n";
};
page.settings.userAgent = require('system').args[1];
page.open(url, function () {
	console.log("<HEADER>[[" + headers + "]]</HEADER>");
	console.log(page.content);
	phantom.exit();
});