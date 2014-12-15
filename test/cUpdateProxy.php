<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 29.01.14
 * Time: 9:29
 * Email: bpteam22@gmail.com
 */

require_once dirname(__FILE__) . '/cnfg_test.php';
use GetContent\cUpdateProxy as cUpdateProxy;
echo "cUpdateProxy<br/>\n";
set_time_limit(0);

define('CHECK_URL', 'http://free-lance.dyndns.info/proxy_chek.php');
$prefix = 'cUpdateProxy_';

$functions = array(

);


runTest($functions, $prefix);


$updateProxy = new cUpdateProxy(CHECK_URL);
$start = microtime(true);
echo date("[H:i:s Y/m/d]", $start)."\n<br>\n";
echo "<table border='1'>";
foreach($updateProxy->getAllSiteSourceName() as $sourceName){
	echo "<tr>";
	echo "<td> $sourceName ";
	$funStart = microtime(true);
	echo "</td>";
	if($result){
		echo "<td> success </td>";
	} else {
		echo "<td> <b> ERROR </b> </td>";
	}
	$funTime = microtime(true) - $funStart;
	echo " <td> $funTime </td>";
	echo "</tr>";
}
echo "</table>";
$end = microtime(true);
echo date('[H:i:s Y/m/d]', $end)."\n<br>\n";
echo '[~'.($end-$start).']';