<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 03.02.14
 * Time: 10:57
 * Email: bpteam22@gmail.com
 */

require_once dirname(__FILE__) . "/../include.php";

use GetContent\cGetContent as cGetContent;

$gc = new cGetContent();

$gc->setLoader('curl');
$gc->load('http://ya.ru'); // Get content use curl

$gc->setLoader('phantom');
$gc->load('http://ya.ru'); // Render page in phantomjs, page render without images. If you need to load image use curl