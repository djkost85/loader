<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 19.08.14
 * Time: 16:29
 * Email: bpteam22@gmail.com
 */
require_once __DIR__ . '/cnfg_test.php';
use GetContent\cMultiCurl as cMultiCurl;
$maxIterations = 7;
$gc = new cMultiCurl();
$url = array(
	'vk.com',
	'mail.ru',
	'torg.ua',
	//'google.com.ua',
	'ya.ru',
	'vk.com',
	'odnoklassniki.ru',
	'olx.ua',
	'vk.lg.ua',
);
for($i=0; $i < $maxIterations; $i++){
	$needEncoding = 'UTF-8';
	$gc->setEncodingAnswer(false);
	$gc->load($url);
	$gc->setEncodingAnswer(true);
	$gc->setEncodingName($needEncoding);
	$gc->load($url);
}