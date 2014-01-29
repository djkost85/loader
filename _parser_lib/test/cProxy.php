<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 26.01.14
 * Time: 0:08
 * Project: get_content
 * @author: Evgeny Pynykh <bpteam22@gmail.com>
 * @link bpteam.net
 */

require_once dirname(__FILE__) . '/cnfg_test.php';
use GetContent\cProxy as cProxy;
echo "cProxy<br/>\n";

$functions = array(
	'createListListExist',
	'addProxySelectListGetProxy',
);

runTest($functions, 'cProxy_');

function cProxy_createListListExist(){
	$listName = 'bpteam';
	$checkUrl = 'http://bpteam.net';
	$checkWord = array('%bpteam22\@gmail\.com%ims', '%380632359213%ms');
	$function = array();
	$needUpdate = true;
	$proxy = new cProxy();
	$proxy->createList($listName, $checkUrl, $checkWord, $function, $needUpdate);
	return $proxy->listExist($listName);
}

function cProxy_addProxySelectListGetProxy(){
	$listName = 'bpteam';
	$proxyIp = '127.0.0.1:8080';
	$properties = array(
		'anonym' => false,
		'referer' => true,
		'post' => true,
		'get' => true,
		'cookie' => false,
		'country' => 'China',
		'last_check' => 13255444887,
		'starttransfer' => 21,
		'upload_speed' => 1,
		'download_speed' => 2,
		'source' => array('proxy.net'),
		'protocol' => array('http'),
	);
	$proxy = new cProxy();
	$proxy->selectList($listName);
	$proxy->addProxy($proxyIp, $properties);
	$getProxy = $proxy->getProxy();
	return $getProxy == $proxyIp;
}

function cProxy_loadProxy(){
	return false;
}