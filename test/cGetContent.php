<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 02.02.14
 * Time: 11:13
 * Project: get_content
 * @author: Evgeny Pynykh <bpteam22@gmail.com>
 * @link bpteam.net
 */
require_once dirname(__FILE__) . '/cnfg_test.php';
use GetContent\cGetContent as cGetContent;
echo "cGetContent<br/>\n";

define('FILE_NAME', dirname(__FILE__).'/support/testCFile.txt');

$functions = array(
	'getContentCurl',
	'getContentPhantom',
	'getContentCurlToPhantom',
	'getContentPhantomToCurl',
);

runTest($functions, 'cGetContent_');

function cGetContent_getContentCurl(){
	$gc = new cGetContent();
	$gc->setLoader('curl');
	$answer = $gc->load('http://ya.ru');
	return preg_match('%yandex%ims',$answer);
}

function cGetContent_getContentPhantom(){
	$gc = new cGetContent();
	$gc->setLoader('phantom');
	$answer = $gc->load('http://ya.ru');
	return preg_match('%yandex%ims',$answer);
}

function cGetContent_getContentCurlToPhantom(){
	$gc = new cGetContent();
	$gc->setLoader('curl');
	$gc->load('http://ya.ru');
	$gc->setLoader('phantom');
	$cookies = $gc->cookie->fromFilePhantomJS();
	return isset($cookies['yandexuid']);
}

function cGetContent_getContentPhantomToCurl(){
	$gc = new cGetContent();
	$gc->setLoader('phantom');
	$gc->load('http://ya.ru');
	$gc->setLoader('curl');
	$cookies = $gc->cookie->fromFileCurl();
	return isset($cookies['yandexuid']);
}