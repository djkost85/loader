/**
 * Created by EC_l on 16.01.14.
 */
var page = require('webpage').create();
page.viewportSize = { width: require('system').args[3], height: require('system').args[4] };
page.settings.userAgent = require('system').args[1];
page.open(require('system').args[2], function () {
    console.log(page.renderBase64(require('system').args[5]));
    phantom.exit();
});