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
$start = time();
echo date('[H:i:s Y/m/d]', $start);
$proxy= new cUpdateProxy('http://track.hamstersgangsters.com/proxy_check.php', 8888);
$proxy->updateDefaultList();
$end = time();
$text = "\n";
$proxy->selectList($proxy->getDefaultListName());
$list = $proxy->getList();
$subject = count($list['content']);
$nameList = $proxy->getDefaultListName();
$proxy->selectList($nameList);
$list = $proxy->getList();
$text .= "$nameList " . count($list['content']) . "\n";
echo date('[H:i:s Y/m/d]', $end);
$time = round(($end-$start)/60);
echo $text = $time." m  $text";
function sendMessage(){
	global $text;
	mail("zking.nothingz@gmail.com", "update default proxy", $text);
}