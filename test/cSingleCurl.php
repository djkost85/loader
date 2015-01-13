<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 13.01.14
 * Time: 14:22
 * Email: bpteam22@gmail.com
 */

require_once __DIR__ . '/cnfg_test.php';
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
	$curl = new cSingleCurl();
	$descriptor =& $curl->getDescriptor();
	return is_resource($descriptor['descriptor']);
}

function cSingleCurl_setOption(){
	$curl = new cSingleCurl();
	$descriptor=& $curl->getDescriptor();
	$curl->setOption($descriptor, CURLOPT_TIMEOUT, 5);
	$check1 = $descriptor['option'][CURLOPT_TIMEOUT] == 5;
	$curl->setOption($descriptor, CURLOPT_TIMEOUT);
	$check2 = $descriptor['option'][CURLOPT_TIMEOUT] == $curl->getDefaultOption(CURLOPT_TIMEOUT);
	return $check1 && $check2;
}

function cSingleCurl_setOptions(){
	$options = array(
		CURLOPT_TIMEOUT => 20,
		CURLOPT_POSTFIELDS => 'qwer=1234&asdf=5678',
	);
	$curl = new cSingleCurl();
	$curl->setOptions($curl->getDescriptor(), $options);
	$descriptor =& $curl->getDescriptor();
	return $descriptor['option'][CURLOPT_TIMEOUT] == $options[CURLOPT_TIMEOUT]
	       && $descriptor['option'][CURLOPT_POST]
	       && $descriptor['option'][CURLOPT_POSTFIELDS] == $options[CURLOPT_POSTFIELDS];
}

function cSingleCurl_getContent(){
	$curl = new cSingleCurl();
	$curl->load('ya.ru');
	$answer = $curl->getAnswer();
	$answer2 = $curl->load('vk.com');
	return preg_match('%yandex%ims', $answer) && preg_match('%vk\.com%ims', $answer2);
}

function cSingleCurl_getHeader(){
	$curl = new cSingleCurl();
	$curl->load('ya.ru', '%yandex%ims');
	$descriptor = $curl->getDescriptor();
	return $descriptor['info']['header'];
}

function cSingleCurl_setReferer(){
	$url = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']).'/support/referer.php';
	$referer = 'http://iamreferer.net';
	$curl = new cSingleCurl();
	$curl->setDefaultOption(CURLOPT_PORT, 8888);
	$curl->setReferer($referer);
	$curl->load($url);
	$text = $curl->getAnswer();
	return preg_match('%iamreferer%ims', $text);
}

function cSingleCurl_usePort(){
	$curl = new cSingleCurl();
	$curl->setDefaultOption(CURLOPT_PORT, 8888);
	$curl->load('localhost');
	$answer = $curl->getAnswer();
	return preg_match('%380632359213%ims', $answer);
}
