<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 19.08.14
 * Time: 16:29
 * Email: bpteam22@gmail.com
 */
require_once dirname(__FILE__) . '/cnfg_test.php';
use GetContent\cMultiCurl as cMultiCurl;
$maxIterations = 7;
for($i=0; $i < $maxIterations; $i++){
	$url = array('vk.com', 'mail.ru');
	$needEncoding = 'UTF-8';
	$gc = new cMultiCurl();
	$gc->setEncodingAnswer(false);
	$gc->load($url);
	$gc->setEncodingAnswer(true);
	$gc->setEncodingName($needEncoding);
	$gc->load($url);
}