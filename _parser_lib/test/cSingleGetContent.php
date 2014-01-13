<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 13.01.14
 * Time: 14:22
 * Email: bpteam22@gmail.com
 */

require_once dirname(__FILE__) . '/cnfg.php';
use GetContent\cSingleGetContent as cSingleGetContent;

$functions = array(
	'init',
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
	$gc = new cSingleGetContent();
	$gc->init();
	$descriptor =& $gc->getDescriptor();
	return is_resource($descriptor['descriptor']);
}

function setOption(){
	$gc = new cSingleGetContent();
	$gc->setOption($gc->getDescriptor(), CURLOPT_TIMEOUT, 5);
	$descriptor=& $gc->getDescriptor();
	$check1 = $descriptor['option'][CURLOPT_TIMEOUT] == 5;
	$gc->setOption($gc->getDescriptor(), CURLOPT_TIMEOUT);
	$check2 = $descriptor['option'][CURLOPT_TIMEOUT] == $gc->getDefaultSetting(CURLOPT_TIMEOUT);
	return $check1 && $check2;
}

function setOptions(){
	$options = array(
		CURLOPT_TIMEOUT => 20,
		CURLOPT_POSTFIELDS => 'qwer=1234&asdf=5678',
	);
	$gc = new cSingleGetContent();
	$gc->setOptions($gc->getDescriptor(), $options);
	$descriptor =& $gc->getDescriptor();
	return $descriptor['option'][CURLOPT_TIMEOUT] == $options[CURLOPT_TIMEOUT] && $descriptor['option'][CURLOPT_POST] && $descriptor['option'][CURLOPT_POSTFIELDS] == $options[CURLOPT_POSTFIELDS];
}