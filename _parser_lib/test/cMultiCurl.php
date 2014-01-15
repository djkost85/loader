<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 13.01.14
 * Time: 14:25
 * Email: bpteam22@gmail.com
 */

require_once dirname(__FILE__) . '/cnfg.php';
use GetContent\cMultiCurl as cMultiCurl;
echo "cMultiCurl<br/>\n";

$functions = array(
	'init',
	'getContent',
	'checkAnswerValid',
	'prepareContent',
);

runTest($functions);

function init(){
	$gc = new cMultiCurl();
	$descriptor =& $gc->getDescriptor();
	$gc->setCountStream(2);
	$descriptorArray =& $gc->getDescriptorArray();
	return is_resource($descriptor['descriptor'])
	       && is_resource($descriptorArray[0]['descriptor'])
	       && is_resource($descriptorArray[1]['descriptor']);
}

function getContent(){
	$url = array(
		'vk.com',
		'ya.ru'
	);
	$gc = new cMultiCurl();
	$gc->getContent($url);
	$answer = $gc->getAnswer();
	return preg_match('%vk\.me%ims', $answer[0]) && preg_match('%yandex%ims', $answer[1]);
}

function checkAnswerValid() {
	$url = array('ya.ru','ya.ru');
	$gc = new cMultiCurl();
	$gc->setCheckAnswer(true);
	$gc->setMinSizeAnswer(1000);
	$gc->getContent($url);
	$answerTrue = $gc->getAnswer();
	$gc->setMinSizeAnswer(strlen($answerTrue[1]) + 100000);
	$gc->getContent($url);
	$answerFalse = $gc->getAnswer();
	return $answerTrue[1] && (!isset($answerFalse[1]) || !$answerFalse[1]);
}

function prepareContent(){
	$url = array('vk.com');
	$originalEncoding = 'windows-1251';
	$needEncoding = 'UTF-8';
	$gc = new cMultiCurl();
	$gc->setEncodingAnswer(false);
	$gc->getContent($url);
	$encoding1 = \GetContent\cStringWork::getEncodingName(current($gc->getAnswer()));
	$gc->setEncodingAnswer(true);
	$gc->setEncodingName($needEncoding);
	$gc->getContent($url);
	$encoding2 = \GetContent\cStringWork::getEncodingName(current($gc->getAnswer()));
	return $encoding1 == $originalEncoding && $needEncoding == $encoding2;
}