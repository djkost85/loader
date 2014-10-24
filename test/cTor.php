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
