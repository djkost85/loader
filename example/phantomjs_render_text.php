<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 31.01.14
 * Time: 14:24
 * Email: bpteam22@gmail.com
 */

require_once __DIR__ . "/../include.php";

use GetContent\cPhantomJS as cPhantomJS;

$phantomJS = new cPhantomJS(PHANTOMJS_EXE);
$text = $phantomJS->renderText('http://ya.ru');
echo $text; // rendered page of ya.ru
