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
$proxy= new cUpdateProxy('http://66.225.221.237/proxy_check.php', 8888);
$proxy->updateArchive();
$proxy->updateDefaultList($countStream);
$end = microtime(true);
$text = "\n";
$proxy->selectList($proxy->getDefaultListName());
$list = $proxy->getList();
$subject = count($list['content']);
$nameList = $proxy->getDefaultListName();
$proxy->selectList($nameList);
$list = $proxy->getList();
$text .= "$nameList " . count($list['content']) . "\n";
echo date('[H:i:s Y/m/d]', $end);
$time = $end-$start;
echo $text = $time." sec \n count stream $countStream \n $text";
function sendMessage(){
	global $text;
	mail("zking.nothingz@gmail.com", "update default proxy", $text);
}