/**
 * Created by EC on 19.01.14.
 */
phantom.addCookie(JSON.parse(require('system').args[1]));
phantom.exit();