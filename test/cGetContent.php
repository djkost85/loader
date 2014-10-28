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
	$gc->setLoader('cPhantomJS');
	$cookies = $gc->moveCookies();
	return isset($cookies['yandexuid']);
}

function cGetContent_getContentPhantomToCurl(){
	$gc = new cGetContent();
	$gc->setLoader('cPhantomJS');
	$gc->load('http://ya.ru');
	$gc->setLoader('cSingleCurl');
	$cookies = $gc->moveCookies();
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
	$url = 'http://www.aptechka.ru/programs/social_card.shtml0';
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
	$ipPull = array('66.225.221.237', '66.225.221.238');
	$tor = new \GetContent\cTor();
	$tor->start();
	$gc = new cGetContent('cSingleCurl');
	$gc->setDefaultOption(CURLOPT_TIMEOUT,90);
	$gc->setUseProxy($tor->getTorConnection(), CURLPROXY_SOCKS5);
	$answer = $gc->load('bpteam.net');
	$answer2 = $gc->load('2ip.ru');
	$newIp = \GetContent\cStringWork::getIp($answer2);
	return  preg_match('%380632359213%ims', $answer) && $newIp[0] && !in_array($newIp[0], $ipPull);
}

function cGetContent_useTorMulti(){
	$ipPull = array('66.225.221.237', '66.225.221.238');
	$tor = new \GetContent\cTor();
	$tor->start();
	$gc = new cGetContent('cMultiCurl');
	$gc->setDefaultOption(CURLOPT_TIMEOUT,90);
	$gc->setUseProxy($tor->getTorConnection(), CURLPROXY_SOCKS5);
	$gc->setDefaultOption(CURLOPT_PORT, 8888);
	$answer = $gc->load('bpteam.net');
	$answer2 = $gc->load(array('2ip.ru','2ip.com.ua', 'myip.ru'));
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
	$gc = new cGetContent('cMultiCurl');
	$tor = new \GetContent\cTor();
	$tor->start();
	$gc->setMinSizeAnswer(2);
	$gc->setWaitExecMSec(500000);
	$gc->setDefaultOption(CURLOPT_PORT,8888);
	$gc->setUseProxy($tor->getTorConnection(), CURLPROXY_SOCKS5);
	$gc->setCheckAnswer(true);
	$gc->setDefaultOption(CURLOPT_TIMEOUT, 60);
	$gc->setTypeContent(\GetContent\cHeaderHTTP::TYPE_CONTENT_TEXT);
	if(isset($_SERVER['HTTP_HOST']) && isset($_SERVER['SCRIPT_NAME'])) {
		$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/support/header.php';
	} else {
		$url = 'http://track.hamstersgangsters.com/_coolLib/loader/test/support/header.php';
	}
	$urls = array($url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,$url,);
	echo "load 1 \n";
	$gc->load($urls);
	echo "load 2 \n";
	$answer = $gc->load($urls);
	var_dump($answer[0]);
	echo "tor restart \n";
	sleep(5);
	$tor->restart();
	echo "load 3 \n";
	$answer = $gc->load($urls);
	var_dump($answer[0]);
}