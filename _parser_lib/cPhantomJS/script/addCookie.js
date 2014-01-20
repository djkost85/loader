/**
 * Created by EC on 19.01.14.
 */
var page = require('webpage').create();

console.log(JSON.stringify(phantom.cookies, null, 2));
console.log(JSON.stringify(JSON.parse(require('system').args[1]),null,2));
var cookies = JSON.parse(require('system').args[1]);
for (key in cookies) {
    console.log(JSON.stringify(cookies[key], null, 2));
    console.log(phantom.addCookie(cookies[key]));
    console.log(page.addCookie(cookies[key]));
}
console.log(JSON.stringify(page.cookies, null, 2));
page.open('about://', function(){
    console.log(page.content);
    console.log(JSON.stringify(phantom.cookies, null, 2));
    phantom.exit();
});
