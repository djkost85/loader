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
);

runTest($functions);

function init(){
	$gc = new cMultiCurl();
	$gc->init();
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
	$answer = $gc->getContent($url);
	return preg_match('%vk\.me%ims', $answer[0]) && preg_match('%yandex%ims', $answer[1]);
}

