/**
 * Created by EC_l on 16.01.14.
 */
var page = require('webpage').create(),
    args = require('system').args;
page.open(args[1], function() {
    page.render(args[2]);
    phantom.exit();
});