/**
 * Created by EC_l on 16.01.14.
 */
var page = require('webpage').create();
var url = require('system').args[3];
var file = require('system').args[4];
page.paperSize = {
    format: require('system').args[5],
    orientation: require('system').args[6],
    margin: require('system').args[7]
};
page.customHeaders = {
    "Referer" : require('system').args[2]
};
page.settings.userAgent = require('system').args[1];
page.open(url, function () {
	page.render(file);
	phantom.exit();
});