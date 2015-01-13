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
	$gc = new cMultiCurl();
	$descriptor =& $gc->getDescriptor();
	$gc->setCountStream(2);
	$descriptorArray =& $gc->getDescriptorArray();
	return is_resource($descriptor['descriptor'])
	       && is_resource($descriptorArray[0]['descriptor'])
	       && is_resource($descriptorArray[1]['descriptor']);
}

function cMultiCurl_getContent(){
	$url = array(
		'vk.com',
		'ya.ru'
	);
	$gc = new cMultiCurl();
	$gc->setCountStream(5);
	$gc->load($url);
	$answer = $gc->getAnswer();
	return preg_match('%vk\.me%ims', $answer[0]) && preg_match('%yandex%ims', $answer[1]);
}

