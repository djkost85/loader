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

$start = time();
echo date('[H:i:s Y/m/d]', $start);
$proxy= new cUpdateProxy('http://free-lance.dyndns.info/proxy_check.php');
$proxy->updateAllList(true);
$end = time();
$text = "\n";
$proxy->selectList($proxy->getDefaultListName());
$list = $proxy->getList();
$subject = count($list['content']);
foreach($proxy->getAllNameList() as $nameList){
	$proxy->selectList($nameList);
	$list = $proxy->getList();
	$text .= "$nameList " . count($list['content']) . "\n";
}
echo date('[H:i:s Y/m/d]', $end);
$time = round(($end-$start)/60);
echo $echo = $time." m  $text";
mail("zking.nothingz@gmail.com", "update proxy $time m $subject", $echo);