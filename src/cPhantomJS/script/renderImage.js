/**
 * Created by EC_l on 16.01.14.
 */
var page = require('webpage').create();
var url = require('system').args[2];
var picFormat = require('system').args[5];
page.viewportSize = {
    width: require('system').args[3],
    height: require('system').args[4]
};
page.settings.userAgent = require('system').args[1];
page.open(url, function () {
    console.log(page.renderBase64(picFormat));
    phantom.exit();
});