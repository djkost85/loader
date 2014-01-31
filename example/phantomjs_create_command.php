<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 31.01.14
 * Time: 14:44
 * Email: bpteam22@gmail.com
 */
require_once dirname(__FILE__) . "/../include.php";

use GetContent\cPhantomJS as cPhantomJS;

$phantomJS = new cPhantomJS(PHANTOMJS_EXE);
/* in command line
/dir_to_phantom/phantomjs [options] [script name] [arguments]
*/

$phantomJS->setOption('load-images', 'false');
/* in command line
/dir_to_phantom/phantomjs --load-images=false [script name] [arguments]
*/

$phantomJS->setArguments(array('client user agent','http://ya.ru','1280','720'));
/* in command line
/dir_to_phantom/phantomjs --load-images=false [script name] 'client user agent' 'http://ya.ru' '1280' '720'
*/

$phantomJS->setScriptName('renderText');
/* in command line
/path_to_phantom/phantomjs --load-images=false /path_to_script/renderText.js 'client user agent' 'http://ya.ru' '1280' '720'
*/

echo $phantomJS->createCommand(); // /path_to_phantom/phantomjs --load-images=false /path_to_script/renderText.js 'client user agent' 'http://ya.ru' '1280' '720'

echo $phantomJS->execCommand($phantomJS->createCommand()); // rendered page of ya.ru
// or use function cPhantomJS::renderText($path, $screenWidthPx, $screenHeightPx)