<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 17.10.2014
 * Time: 10:00
 * Project: fo_realty
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

require_once __DIR__ . '/cnfg_test.php';
echo "cSimpleHTTP<br/>\n";


$functions = array(
	//'load',
	'useTor',
);

runTest($functions, 'cSimpleHTTP_');

function cSimpleHTTP_load(){

}

function cSimpleHTTP_useTor(){
	$yourIpPull = array('66.225.221.237', '66.225.221.238');
	$simpleHTTP = new GetContent\cSimpleHTTP();
	$tor = new GetContent\cTor();
	$tor->start();
	$simpleHTTP->setUseProxy($tor->getTorConnection());
	$answer = $simpleHTTP->load('http://2ip.ru');
	$newIp = \GetContent\cStringWork::getIp($answer);
	//echo (isset($newIp[0])?$newIp[0]:'not found IP')."\n";
	return !in_array($newIp[0], $yourIpPull);
}