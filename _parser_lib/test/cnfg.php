<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 09.01.14
 * Time: 12:01
 * Email: bpteam22@gmail.com
 */
ini_set('default_charset', 'utf-8');
ini_set('display_errors',1);
error_reporting(E_ALL);
require_once dirname(__FILE__) . '/../include.php';

function runTest($functions){
	$start = microtime(true);
	echo date("[H:i:s Y/m/d]", $start)."\n<br>\n";
	foreach($functions as $function){
		echo $function;
		if($function()){
			echo " success \n<br>\n";
		} else {
			echo " <b>ERROR</b> \n<br>\n";
			exit();
		}
	}
	$end = microtime(true);
	echo date('[H:i:s Y/m/d]', $end)."\n<br>\n";
	echo '[~'.($end-$start).']';
}