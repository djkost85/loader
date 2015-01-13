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
require_once __DIR__ . '/cnfg_test.php';
use GetContent\cGetContent as cGetContent;
echo "cGetContent<br/>\n";

define('FILE_NAME', __DIR__.'/support/testCFile.txt');

$functions = array(
	'getContentSingleCurl',
	'getContentPhantom',
	'getContentCurlToPhantom', //TODO wont fix
	'getContentPhantomToCurl', //TODO wont fix
	'checkAnswerValid',
	'prepareContent',
	'useTor',
	'useTorMulti',
	//'checkTorRestartMultiCurl',
);

runTest($functions, 'cGetContent_');

function cGetContent_getContentSingleCurl(){
	$getContent = new cGetContent();
	$getContent->setLoader('cSingleCurl');
	$answer = $getContent->load('http://ya.ru');
	return preg_match('%yandex%ims',$answer);
}

function cGetContent_getContentPhantom(){
	$getContent = new cGetContent();
	$getContent->setLoader('cPhantomJS');
	$answer = $getContent->load('http://ya.ru');
	return preg_match('%yandex%ims',$answer);
}

function cGetContent_getContentCurlToPhantom(){
	$getContent = new cGetContent();
	$getContent->setLoader('cSingleCurl');
	$getContent->load('http://ya.ru');
	$getContent->setLoader('cPhantomJS');
	$cookies = $getContent->moveCookies();
	return isset($cookies['yandexuid']);
}

function cGetContent_getContentPhantomToCurl(){
	$getContent = new cGetContent();
	$getContent->setLoader('cPhantomJS');
	$getContent->load('http://ya.ru');
	$getContent->setLoader('cSingleCurl');
	$cookies = $getContent->moveCookies();
	return isset($cookies['yandexuid']);
}

function cGetContent_checkAnswerValid() {
	$url = 'ya.ru';
	$getContent = new cGetContent();
	$getContent->setCheckAnswer(true);
	$getContent->setMinSizeAnswer(1000);
	$answerTrue = $getContent->load($url);
	$getContent->setMinSizeAnswer(strlen($answerTrue) + 1000000);
	$answerFalse = $getContent->load($url);
	return $answerTrue && !(bool)$answerFalse;
}

function cGetContent_prepareContent(){
	$url = 'http://www.aptechka.ru/programs/social_card.shtml0';
	$withoutEncoding = 'windows-1251';
	$needEncoding = 'UTF-8';
	$getContent = new cGetContent();
	$getContent->setEncodingAnswer(false);
	$answer = $getContent->load($url);
	$encoding1 = \GetContent\cStringWork::getEncodingName($answer);
	$getContent->setEncodingAnswer(true);
	$getContent->setEncodingName($needEncoding);
	$answer = $getContent->load($url);
	$encoding2 = \GetContent\cStringWork::getEncodingName($answer);
	return $encoding1 == $withoutEncoding && $needEncoding == $encoding2;
}

function cGetContent_useTor(){
	$ipPull = array('66.225.221.237', '66.225.221.238', '93.73.209.34');
	$tor = new \GetContent\cTor();
	$tor->start();
	$getContent = new cGetContent('cSingleCurl');
	$getContent->setDefaultOption(CURLOPT_TIMEOUT,90);
	$getContent->setUseProxy($tor->getTorConnection(), CURLPROXY_SOCKS5);
	$answer = $getContent->load('bpteam.net');
	$answer2 = $getContent->load('2ip.ru');
	$newIp = \GetContent\cStringWork::getIp($answer2);
	return  preg_match('%380632359213%ims', $answer) && $newIp[0] && !in_array($newIp[0], $ipPull);
}

function cGetContent_useTorMulti(){
	$ipPull = array('66.225.221.237', '66.225.221.238');
	$tor = new \GetContent\cTor();
	$tor->start();
	$getContent = new cGetContent('cMultiCurl');
	$getContent->setDefaultOption(CURLOPT_TIMEOUT,90);
	$getContent->setUseProxy($tor->getTorConnection(), CURLPROXY_SOCKS5);
	$getContent->setDefaultOption(CURLOPT_PORT, 8888);
	$answer = $getContent->load('bpteam.net');
	$answer2 = $getContent->load(array('2ip.ru','2ip.com.ua', 'myip.ru'));
	$newIp = \GetContent\cStringWork::getIp($answer2[0]);
	$newIp1 = \GetContent\cStringWork::getIp($answer2[1]);
	$newIp2 = \GetContent\cStringWork::getIp($answer2[2]);
	var_dump($newIp,$newIp1,$newIp2);
	$newIp = $newIp[0] && !in_array($newIp[0], $ipPull);
	$newIp1 = $newIp1[0] && !in_array($newIp1[0], $ipPull);
	$newIp2 = $newIp2[0] && !in_array($newIp2[0], $ipPull);
	return  preg_match('%380632359213%ims', $answer[0]) && $newIp && $newIp1 && $newIp2;
}

function cGetContent_checkTorRestartMultiCurl(){
	$getContent = new cGetContent('cMultiCurl');
	$tor = new \GetContent\cTor();
	$tor->start();
	$getContent->setMinSizeAnswer(2);
	$getContent->setWaitExecMSec(500000);
	$getContent->setDefaultOption(CURLOPT_PORT,8888);
	$getContent->setUseProxy($tor->getTorConnection(), CURLPROXY_SOCKS5);
	$getContent->setCheckAnswer(true);
	$getContent->setDefaultOption(CURLOPT_TIMEOUT, 60);
	$getContent->setTypeContent(\GetContent\cHeaderHTTP::TYPE_CONTENT_TEXT);
	if(isset($_SERVER['HTTP_HOST']) && isset($_SERVER['SCRIPT_NAME'])) {
		$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/support/header.php';
	} else {
		$url = 'http://track.hamstersgangsters.com/_coolLib/loader/test/support/header.php';
	}
	$urls = array($url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,);
	echo "load 1 \n";
	$getContent->load($urls);
	echo "load 2 \n";
	$answer = $getContent->load($urls);
	var_dump($answer[0]);
	echo "tor restart \n";
	sleep(5);
	$tor->restart();
	echo "load 3 \n";
	$answer = $getContent->load($urls);
	var_dump($answer[0]);
}