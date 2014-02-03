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

$gc->setMode('curl');
$gc->getContent('http://ya.ru'); // Get content use curl

$gc->setMode('phantom');
$gc->getContent('http://ya.ru'); // Render page in phantomjs, page render without images. If you need to load image use curl