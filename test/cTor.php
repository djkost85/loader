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
echo "cTor<br/>\n";

$functions = array(
	/*'searchFreePort',
	'createConfig',
	'getTorConnection',
	'start',
	'stop',
	'restart',
	'stopAll',*/
	//'setTorCountry',
	'lookHeaders',
);

runTest($functions, 'cTor_');

function cTor_searchFreePort(){
	$tor = new cTor();
	return $tor::KEY_PULL_START <= $tor->getPort() && $tor::KEY_PULL_END >= $tor->getPort();
}

function cTor_createConfig(){
	$tor = new cTor();
	$tor2 = new cTor();
	$tor->createConfig();
	return !$tor2->isFreePort($tor->getPort());
}

function cTor_getTorConnection(){
	$tor = new cTor();
	$tor->createConfig();
	$connect = $tor->getHost() . ':' . $tor->getPort();
	return $connect == $tor->getTorConnection();
}

function cTor_start(){
	$tor = new cTor();
	$tor->start();
	return $tor->isExist();
}

function cTor_stop(){
	$tor = new cTor();
	$tor->start();
	$tor->stop();
	return !$tor->isExist();
}

function cTor_restart(){
	$tor = new cTor();
	$tor->start();
	$res = $tor->isExist();
	$tor->restart();
	return $res && $tor->isExist();
}

function cTor_stopAll(){
	$tor1 = new cTor();
	$tor2 = new cTor();
	$tor3 = new cTor();
	$tor1->start();
	$tor2->start();
	$tor3->start();
	$tor1->stopAll();
	sleep(5);
	return !$tor1->isExist() && !$tor2->isExist() && !$tor3->isExist();
}

function cTor_lookHeaders(){
	echo "<pre>\n";
	$url = 'http://bpteam.net/header.php';
	//$url = 'http://m.torg.ua/kiev/kvartiry/sdam/1-komnatnye';
	/**
	 * @var \GetContent\cGetContent|\GetContent\cSingleCurl $gc
	 */
	$gc = new \GetContent\cGetContent('cSingleCurl');

	$tor = new \GetContent\cTor();
	$tor->setIpCountries('us');
	var_dump($tor->start());
	$gc->setCheckAnswer(false);
	$gc->setMinSizeAnswer(2);
	//$gc->setDefaultOption(CURLOPT_PORT,8888);
	var_dump($tor->getTorConnection());
	$gc->setUseProxy($tor->getTorConnection(), CURLPROXY_SOCKS5);
	$answer = $gc->load($url);
	var_dump($gc->getInfo());
	var_dump($answer);
	echo "</pre>\n";
}

function cTor_setTorCountry(){
	$ipPull = array('66.225.221.237', '66.225.221.238');
	$url = 'icanhazip.com';
	$regEx = '%<td[^>]*>(<br\s*/>)*<strong\s*title="(?<title>[^"]*)">[^<]*</strong>(<br\s*/>)*</td>\s*<td[^>]*>(?<value>[^<]*)</td>%imsu';
	echo "REAL IP IS ". implode(',', $ipPull);
	$tor = new \GetContent\cTor();
	$tor->setIpCountries('ua');
	$gc = new \GetContent\cGetContent('cSingleCurl');
	$gc->setDefaultOption(CURLOPT_TIMEOUT,90);
	var_dump($gc->load($url));
	$tor->start();
	$gc->setUseProxy($tor->getTorConnection(), CURLPROXY_SOCKS5);
	var_dump($gc->load($url));
	$tor->setIpCountries('ca');
	$tor->restart();
	var_dump($gc->load($url));
	$tor->setIpCountries(array('ua'));
	$tor->restart();
	var_dump($gc->load($url));
}