<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 19.08.14
 * Time: 15:11
 * Email: bpteam22@gmail.com
 */
require_once __DIR__ . '/cnfg_test.php';
use GetContent\cSingleCurl as cSingleCurl;
$maxIterations = 10;
for($i=0; $i < $maxIterations; $i++){
	$url = 'vk.com';
	$withoutEncoding = 'windows-1251';
	$needEncoding = 'UTF-8';
	$gc = new cSingleCurl();
	$gc->setEncodingAnswer(false);
	$gc->load($url);
	$gc->setEncodingAnswer(true);
	$gc->setEncodingName($needEncoding);
	$gc->load($url);
}