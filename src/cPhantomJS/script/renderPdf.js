/**
 * Created by EC_l on 16.01.14.
 */
var page = require('webpage').create();
page.paperSize = { format: require('system').args[4], orientation: require('system').args[5], margin: require('system').args[6] };
page.settings.userAgent = require('system').args[1];
page.open(require('system').args[2], function () {
	page.render(require('system').args[3]);
	phantom.exit();
});