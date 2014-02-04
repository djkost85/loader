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
	$gc->setMode('curl');
	$answer = $gc->getContent('http://ya.ru');
	return preg_match('%yandex%ims',$answer);
}

function cGetContent_getContentPhantom(){
	$gc = new cGetContent();
	$gc->setMode('phantom');
	$answer = $gc->getContent('http://ya.ru');
	return preg_match('%yandex%ims',$answer);
}

function cGetContent_getContentCurlToPhantom(){
	$gc = new cGetContent();
	$gc->setMode('curl');
	$gc->getContent('http://ya.ru');
	$gc->setMode('phantom');
	$cookies = $gc->cookie->fromFilePhantomJS();
	return isset($cookies['yandexuid']);
}

function cGetContent_getContentPhantomToCurl(){
	$gc = new cGetContent();
	$gc->setMode('phantom');
	$gc->getContent('http://ya.ru');
	$gc->setMode('curl');
	$cookies = $gc->cookie->fromFileCurl();
	return isset($cookies['yandexuid']);
}