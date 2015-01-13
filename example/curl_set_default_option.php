<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 03.02.14
 * Time: 10:22
 * Email: bpteam22@gmail.com
 */

require_once __DIR__ . "/../include.php";

use GetContent\cSingleCurl as cSingleCurl;

$curl = new cSingleCurl();

$curl->setDefaultOption(CURLOPT_REFERER, 'http://ya.ru');

$defaultOptions = array(
	CURLOPT_REFERER => 'http://ya.ru',
	CURLOPT_FOLLOWLOCATION => true,
);
$curl->setDefaultOptions($defaultOptions);