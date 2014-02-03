<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 03.02.14
 * Time: 8:42
 * Email: bpteam22@gmail.com
 */
require_once dirname(__FILE__) . "/../include.php";

use GetContent\cSingleCurl as cSingleCurl;

$curl = new cSingleCurl();
$descriptor =& $curl->getDescriptor();
$curl->setOption($descriptor, CURLOPT_REFERER, 'http://ya.ru');