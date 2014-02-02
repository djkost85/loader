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

echo "<a href='index.php'>..</a>";

function runTest($functions, $prefix = ''){
	$start = microtime(true);
	echo date("[H:i:s Y/m/d]", $start)."\n<br>\n";
	echo "<table border='1'>";
	foreach($functions as $function){
		echo "<tr>";
		echo "<td> $function </td>";
		$funStart = microtime(true);
		$nameFunction = $prefix.$function;
		if($nameFunction()){
			echo "<td> success </td>";
		} else {
			echo "<td> <b> ERROR </b> </td>";
			break;
		}
		$funTime = microtime(true) - $funStart;
		echo " <td> $funTime </td>";
		echo "</tr>";
	}
	echo "</table>";
	$end = microtime(true);
	echo date('[H:i:s Y/m/d]', $end)."\n<br>\n";
	echo '[~'.($end-$start).']';
}