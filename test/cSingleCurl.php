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
	'setOption',
	'setOptions',
	'usePort',
	'setReferer',
	'getContent',
	'getHeader',
);

runTest($functions, 'cSingleCurl_');

function cSingleCurl_init(){
	$gc = new cSingleCurl();
	$descriptor =& $gc->getDescriptor();
	return is_resource($descriptor['descriptor']);
}

function cSingleCurl_setOption(){
	$gc = new cSingleCurl();
	$descriptor=& $gc->getDescriptor();
	$gc->setOption($descriptor, CURLOPT_TIMEOUT, 5);
	$check1 = $descriptor['option'][CURLOPT_TIMEOUT] == 5;
	$gc->setOption($descriptor, CURLOPT_TIMEOUT);
	$check2 = $descriptor['option'][CURLOPT_TIMEOUT] == $gc->getDefaultOption(CURLOPT_TIMEOUT);
	return $check1 && $check2;
}

function cSingleCurl_setOptions(){
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

function cSingleCurl_getContent(){
	$gc = new cSingleCurl();
	$gc->load('ya.ru');
	$answer = $gc->getAnswer();
	$answer2 = $gc->load('vk.com');
	return preg_match('%yandex%ims', $answer) && preg_match('%vk\.com%ims', $answer2);
}

function cSingleCurl_getHeader(){
	$gc = new cSingleCurl();
	$gc->load('ya.ru', '%yandex%ims');
	$descriptor = $gc->getDescriptor();
	return $descriptor['info']['header'];
}

function cSingleCurl_setReferer(){
	$url = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']).'/support/referer.php';
	$referer = 'http://iamreferer.net';
	$gc = new cSingleCurl();
	$gc->setDefaultOption(CURLOPT_PORT, 8888);
	$gc->setReferer($referer);
	$gc->load($url);
	$text = $gc->getAnswer();
	return preg_match('%iamreferer%ims', $text);
}

function cSingleCurl_usePort(){
	$gc = new cSingleCurl();
	$gc->setDefaultOption(CURLOPT_PORT, 8888);
	$gc->load('track.hamstersgangsters.com', '%380632359213%ims');
	$answer = $gc->getAnswer();
	return preg_match('%380632359213%ims', $answer);
}
