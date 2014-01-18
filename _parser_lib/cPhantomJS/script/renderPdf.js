/**
 * Created by EC_l on 16.01.14.
 */
var page = require('webpage').create();
page.paperSize = { format: require('system').args[3], orientation: require('system').args[4], margin: require('system').args[5] };
page.open(require('system').args[1], function () {
	page.render(require('system').args[2]);
	phantom.exit();
});