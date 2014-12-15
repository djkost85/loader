<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 31.01.14
 * Time: 14:33
 * Email: bpteam22@gmail.com
 */

require_once dirname(__FILE__) . "/../include.php";

use GetContent\cPhantomJS as cPhantomJS;

$phantomJS = new cPhantomJS(PHANTOMJS_EXE);

$phantomJS->setOption('load-images', 'false'); // in command line --load-images=false

$text = $phantomJS->renderText('http://ya.ru');
echo $text; // rendered page of ya.ru without images