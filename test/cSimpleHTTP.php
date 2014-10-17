<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 17.10.2014
 * Time: 10:00
 * Project: fo_realty
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

require_once dirname(__FILE__) . '/cnfg_test.php';
use GetContent\cGetContent as cGetContent;
echo "cGetContent<br/>\n";

define('FILE_NAME', dirname(__FILE__).'/support/testCFile.txt');

$functions = array(
	//'load',
	'useTor',
);

runTest($functions, 'cSimpleHTTP_');

function cSimpleHTTP_load(){

}

function cSimpleHTTP_useTor(){
	$simpleHTTP = new GetContent\cSimpleHTTP();
	$simpleHTTP->setUseProxy('127.0.0.1:20001');
	$answer = $simpleHTTP->load('http://2ip.ru');
	$newIp = \GetContent\cStringWork::getIp($answer);
	echo (isset($newIp[0])?$newIp[0]:'not found IP')."\n";
}