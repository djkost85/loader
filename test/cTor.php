<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 15.10.2014
 * Time: 10:09
 * Project: fo_realty
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
require_once dirname(__FILE__) . '/cnfg_test.php';

use GetContent\cTor as cTor;
use GetContent\cGetContent as cGetContent;
echo "cSingleCurl<br/>\n";

$functions = array(
	'searchFreePort',
	'createConfig',
	'getTorConnection',
	'start',
	'stop',
	'restart',
	'stopAll',
);

runTest($functions, 'cTor_');

function cTor_searchFreePort(){
	$tor = new cTor();
	return $tor::keyPullStart <= $tor->getPort() && $tor::keyPullEnd >= $tor->getPort();
}

function cTor_createConfig(){
	$tor = new cTor();
	$tor->createConfig();
	return !$tor->isFreePort($tor->getPort());
}

function cTor_getTorConnection(){
	$tor = new cTor();
	$tor->createConfig();
	$connect = $tor->getHost() . ':' . $tor->getPort();
	return $connect == $tor->getTorConnection();
}

function cTor_start(){
	$serverIpPull = array('66.225.221.237','66.225.221.238');
	$tor = new cTor();
	$gc = new cGetContent('cSingleCurl');
	$tor->start();
	$gc->setUseProxy($tor->getTorConnection(), CURLPROXY_SOCKS5);
	$answer = $gc->load('2ip.ru');
	$newIp = \GetContent\cStringWork::getIp($answer);
	echo (isset($newIp[0])?$newIp[0]:'not found IP')."\n";
}

function cTor_stop(){

}

function cTor_restart(){

}

function cTor_stopAll(){

}