<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 31.01.14
 * Time: 13:02
 * Email: bpteam22@gmail.com
 */

require_once dirname(__FILE__)."/../../../include.php";

use GetContent\cUpdateProxy as cUpdateProxy;
register_shutdown_function('sendMessage');
$countStream = 1000;
$start = microtime(true);
echo date('[H:i:s Y/m/d]', $start);
$proxy= new cUpdateProxy('http://hamstersgangsters.com/proxy_check.php', 8888);
$proxy->updateArchive();
$proxyList = $proxy->downloadArchiveProxy();
$countChallengers = count($proxyList['content']);
unset($proxyList);
$proxy->updateDefaultList($countStream);
$end = microtime(true);
$text = "\n";
$nameList = $proxy->getDefaultListName();
$proxy->selectList($nameList);
$list = $proxy->getList();
$countResult = count($list['content']);
$text .= "$nameList " . $countResult . " of $countChallengers " . round(($countResult/$countChallengers)*100) . "%\n";
echo date('[H:i:s Y/m/d]', $end);
$time = $end-$start;
echo $text = $time." sec \n count stream $countStream \n $text";
function sendMessage(){
	global $text;
	mail("zking.nothingz@gmail.com", "update default proxy", $text);
}