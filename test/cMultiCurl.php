<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 13.01.14
 * Time: 14:25
 * Email: bpteam22@gmail.com
 */

require_once __DIR__ . '/cnfg_test.php';
use GetContent\cMultiCurl as cMultiCurl;
echo "cMultiCurl<br/>\n";

$functions = array(
	'init',
	'getContent',
);

runTest($functions, 'cMultiCurl_');

function cMultiCurl_init(){
	$getContent = new cMultiCurl();
	$descriptor =& $getContent->getDescriptor();
	$getContent->setCountStream(2);
	$descriptorArray =& $getContent->getDescriptorArray();
	return is_resource($descriptor['descriptor'])
	       && is_resource($descriptorArray[0]['descriptor'])
	       && is_resource($descriptorArray[1]['descriptor']);
}

function cMultiCurl_getContent(){
	$url = array(
		'vk.com',
		'ya.ru'
	);
	$getContent = new cMultiCurl();
	$getContent->setCountStream(5);
	$getContent->load($url);
	$answer = $getContent->getAnswer();
	return preg_match('%vk\.me%ims', $answer[0]) && preg_match('%yandex%ims', $answer[1]);
}

