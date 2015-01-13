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
ini_set('memory_limit','1024M');
error_reporting(E_ALL);
require_once __DIR__ . '/../include.php';

echo "<a href='index.php'>..</a>";

function runTest($functions, $prefix = ''){
	$start = microtime(true);
	$memStart = memory_get_usage(true);
	echo "<pre>\n";
	echo date("[H:i:s Y/m/d]", $start)."[$memStart]\n\n";
	echo "Method\tResult\ttime\tmemory";
	foreach($functions as $function){
		echo "\n\n";
		echo "$function\t";
		$funStart = microtime(true);
		$memFunStart = memory_get_usage(true);
		$nameFunction = $prefix.$function;
		if($nameFunction()){
			echo "success";
		} else {
			echo "ERROR";
			//break;
		}
		$funTime = microtime(true) - $funStart;
		$memFunEnd = memory_get_usage(true) - $memFunStart;
		gc_collect_cycles();
		$memFunEndAfterGC = memory_get_usage(true) - $memFunStart;
		$memoryFunPeak = memory_get_peak_usage(true);
		echo "\t$funTime";
		echo "\t$memFunEnd ($memFunEndAfterGC)[$memoryFunPeak]";
	}
	echo "</pre>";
	$memEnd = memory_get_usage(true);
	$memoryPeak = memory_get_peak_usage(true);
	$end = microtime(true);
	echo date('[H:i:s Y/m/d]', $end)."\n\n";
	echo '[~'.($end-$start).']';
	echo '['.($memEnd - $memStart).'](' . $memoryPeak .')';
}