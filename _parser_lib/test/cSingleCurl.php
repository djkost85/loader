<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 13.01.14
 * Time: 14:22
 * Email: bpteam22@gmail.com
 */

require_once dirname(__FILE__) . '/cnfg_test.php';
use GetContent\cSingleCurl as cSingleCurl;
echo "cSingleCurl<br/>\n";

$functions = array(
	'init',
	'getRandomUserAgent',
	'setOption',
	'setOptions',
	'getContent',
	'getHeader',
	'mimeType',
	'checkAnswerValid',
	'prepareContent',
);

runTest($functions);

function init(){
	$gc = new cSingleCurl();
	$descriptor =& $gc->getDescriptor();
	return is_resource($descriptor['descriptor']);
}

function getRandomUserAgent(){
	$gc = new cSingleCurl();
	return in_array($gc->getRandomUserAgent(), $gc->getUserAgentList());
}

function setOption(){
	$gc = new cSingleCurl();
	$descriptor=& $gc->getDescriptor();
	$gc->setOption($descriptor, CURLOPT_TIMEOUT, 5);
	$check1 = $descriptor['option'][CURLOPT_TIMEOUT] == 5;
	$gc->setOption($descriptor, CURLOPT_TIMEOUT);
	$check2 = $descriptor['option'][CURLOPT_TIMEOUT] == $gc->getDefaultSetting(CURLOPT_TIMEOUT);
	return $check1 && $check2;
}

function setOptions(){
	$options = array(
		CURLOPT_TIMEOUT => 20,
		CURLOPT_POSTFIELDS => 'qwer=1234&asdf=5678',
	);
	$gc = new cSingleCurl();
	$gc->setOptions($gc->getDescriptor(), $options);
	$descriptor =& $gc->getDescriptor();
	return $descriptor['option'][CURLOPT_TIMEOUT] == $options[CURLOPT_TIMEOUT]
	       && $descriptor['option'][CURLOPT_POST]
	       && $descriptor['option'][CURLOPT_POSTFIELDS] == $options[CURLOPT_POSTFIELDS];
}

function getContent(){
	$gc = new cSingleCurl();
	$gc->getContent('ya.ru', '%yandex%ims');
	$answer = $gc->getAnswer();
	return preg_match('%yandex%ims', $answer);
}

function getHeader(){
	$gc = new cSingleCurl();
	$gc->getContent('ya.ru', '%yandex%ims');
	$descriptor = $gc->getDescriptor();
	return $descriptor['info']['header'];
}

function mimeType(){
	$gc = new cSingleCurl();
	return $gc->mimeType('audio/mpeg', 'file')
	       && $gc->mimeType('image/png', 'img')
	       && $gc->mimeType('text/html', 'html')
	       && !$gc->mimeType('image/png', 'html');
}

function checkAnswerValid() {
	$url = 'ya.ru';
	$gc = new cSingleCurl();
	$gc->setCheckAnswer(true);
	$gc->setMinSizeAnswer(1000);
	$gc->getContent($url);
	$answerTrue = $gc->getAnswer();
	$gc->setMinSizeAnswer(strlen($answerTrue) + 100000);
	$gc->getContent($url);
	$answerFalse = $gc->getAnswer();
	return $answerTrue && !(bool)$answerFalse;
}

function prepareContent(){
	$url = 'vk.com';
	$withoutEncoding = 'windows-1251';
	$needEncoding = 'UTF-8';
	$gc = new cSingleCurl();
	$gc->setEncodingAnswer(false);
	$gc->getContent($url);
	$encoding1 = \GetContent\cStringWork::getEncodingName($gc->getAnswer());
	$gc->setEncodingAnswer(true);
	$gc->setEncodingName($needEncoding);
	$gc->getContent($url);
	$encoding2 = \GetContent\cStringWork::getEncodingName($gc->getAnswer());
	return $encoding1 == $withoutEncoding && $needEncoding == $encoding2;
}