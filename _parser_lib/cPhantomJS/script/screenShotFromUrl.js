/**
 * Created by EC_l on 16.01.14.
 */
var page = require('webpage').create();
page.viewportSize = { width: require('system').args[2], height: require('system').args[3] };
page.open(require('system').args[1], function () {
    console.log(page.renderBase64('PNG'));
    phantom.exit();
});