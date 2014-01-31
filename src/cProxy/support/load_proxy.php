<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 31.01.14
 * Time: 11:40
 * Email: bpteam22@gmail.com
 */
require_once dirname(__FILE__)."/../../include.php";

use GetContent\cUpdateProxy as cUpdateProxy;
set_time_limit(3600);
$proxy = new cUpdateProxy();
$name = 'all';
$proxy->selectList($name);
$list = $proxy->getList();
$functionList = array(
	'cookie',
	'get',
	'post',
	'post',
	'referer',
	'anonym',
	'country'
);
if(isset($_GET['filter'])){
	$function = array();
	foreach($functionList as $functionName){
		if(isset($_GET[$functionName]) && $_GET[$functionName]) $function[$functionName] = $_GET[$functionName];
	}
	$proxyList = $proxy->getProxyByFunction($list['content'],$function);
	foreach($proxyList as $ipProxy){
		$data[] = $ipProxy['proxy'];
	}
	echo implode("\n",$data);
}