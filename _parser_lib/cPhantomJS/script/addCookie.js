/**
 * Created by EC on 19.01.14.
 */
var needCookies = JSON.parse(require('system').args[1]);
for (key in needCookies) {
    console.log(phantom.addCookie(needCookies[key]));
}
var page = require('webpage').create();

console.log(JSON.stringify(phantom.cookies, null, 2));
phantom.exit();
