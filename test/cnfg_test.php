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
	$memStart = memory_get_usage(true);
	echo date("[H:i:s Y/m/d]", $start)."[$memStart]\n<br>\n";
	echo "<table border='1'>";
	echo "<tr><td>Method</td><td>Result</td><td>time</td><td>memory</td></tr>";
	foreach($functions as $function){
		echo "<tr>";
		echo "<td> $function </td>";
		$funStart = microtime(true);
		$memFunStart = memory_get_usage(true);
		$nameFunction = $prefix.$function;
		if($nameFunction()){
			echo "<td> success </td>";
		} else {
			echo "<td> <b> ERROR </b> </td>";
			//break;
		}
		$funTime = microtime(true) - $funStart;
		$memFunEnd = memory_get_usage(true) - $memFunStart;
		gc_collect_cycles();
		$memFunEndAfterGC = memory_get_usage(true) - $memFunStart;
		$memoryFunPeak = memory_get_peak_usage(true);
		echo " <td> $funTime </td>";
		echo "<td> $memFunEnd ($memFunEndAfterGC)[$memoryFunPeak]</td>";
		echo "</tr>";
	}
	echo "</table>";
	$memEnd = memory_get_usage(true);
	$memoryPeak = memory_get_peak_usage(true);
	$end = microtime(true);
	echo date('[H:i:s Y/m/d]', $end)."\n<br>\n";
	echo '[~'.($end-$start).']';
	echo '['.($memEnd - $memStart).'](' . $memoryPeak .')';
}