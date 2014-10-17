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
	//'getContentSingleCurl',
	//'getContentPhantom',
	//'getContentCurlToPhantom', //TODO wont fix
	//'getContentPhantomToCurl', //TODO wont fix
	//'checkAnswerValid',
	//'prepareContent', //TODO wont fix
	'useTor',
);

runTest($functions, 'cGetContent_');

function cGetContent_getContentSingleCurl(){
	$gc = new cGetContent();
	$gc->setLoader('cSingleCurl');
	$answer = $gc->load('http://ya.ru');
	return preg_match('%yandex%ims',$answer);
}

function cGetContent_getContentPhantom(){
	$gc = new cGetContent();
	$gc->setLoader('cPhantomJS');
	$answer = $gc->load('http://ya.ru');
	return preg_match('%yandex%ims',$answer);
}

function cGetContent_getContentCurlToPhantom(){
	$gc = new cGetContent();
	$gc->setLoader('cSingleCurl');
	$gc->load('http://ya.ru');
	$gc->setLoader('phantom');
	$cookies = $gc->cookie->fromFilePhantomJS();
	return isset($cookies['yandexuid']);
}

function cGetContent_getContentPhantomToCurl(){
	$gc = new cGetContent();
	$gc->setLoader('cPhantomJS');
	$gc->load('http://ya.ru');
	$gc->setLoader('cSingleCurl');
	$cookies = $gc->cookie->fromFileCurl();
	return isset($cookies['yandexuid']);
}

function cGetContent_checkAnswerValid() {
	$url = 'ya.ru';
	$gc = new cGetContent();
	$gc->setCheckAnswer(true);
	$gc->setMinSizeAnswer(1000);
	$answerTrue = $gc->load($url);
	$gc->setMinSizeAnswer(strlen($answerTrue) + 1000000);
	$answerFalse = $gc->load($url);
	return $answerTrue && !(bool)$answerFalse;
}

function cGetContent_prepareContent(){
	$url = 'vk.com';
	$withoutEncoding = 'windows-1251';
	$needEncoding = 'UTF-8';
	$gc = new cGetContent();
	$gc->setEncodingAnswer(false);
	$answer = $gc->load($url);
	$encoding1 = \GetContent\cStringWork::getEncodingName($answer);
	$gc->setEncodingAnswer(true);
	$gc->setEncodingName($needEncoding);
	$answer = $gc->load($url);
	$encoding2 = \GetContent\cStringWork::getEncodingName($answer);
	return $encoding1 == $withoutEncoding && $needEncoding == $encoding2;
}

function cGetContent_useTor(){
	$tor = new \GetContent\cTor();
	$tor->start();
	$gc = new cGetContent('cSingleCurl');
	$gc->setDefaultOption(CURLOPT_TIMEOUT,90);
	$gc->setUseProxy($tor->getTorConnection(), CURLPROXY_SOCKS5);
	$answer = $gc->load('bpteam.net');
	return  preg_match('%380632359213%ims', $answer);
}

function setTorCountry(){
	$tor = new \GetContent\cTor();
	$tor->start();
	$gc = new cGetContent('cSingleCurl');
	$gc->setDefaultOption(CURLOPT_TIMEOUT,90);
	$gc->setUseProxy($tor->getTorConnection(), CURLPROXY_SOCKS5);
	$start = time();
	for($i = 0 ; $i < 4; $i++){
		$answer = $gc->load('2ip.ru');
		$newIp = \GetContent\cStringWork::getIp($answer);
		echo (isset($newIp[0])?$newIp[0]:'not found IP')."\n";
		echo '['.(time() - $start) . "]\n";
	}
	return preg_match('%380632359213%ims', $answer);
}