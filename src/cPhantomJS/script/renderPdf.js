/**
 * Created by EC_l on 16.01.14.
 */
var page = require('webpage').create();
var url = require('system').args[2];
var file = require('system').args[3];
page.paperSize = {
    format: require('system').args[4],
    orientation: require('system').args[5],
    margin: require('system').args[6]
};
page.settings.userAgent = require('system').args[1];
page.open(url, function () {
	page.render(file);
	phantom.exit();
});