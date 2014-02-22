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
$proxy->selectList($proxy->getDefaultListName());
$list = $proxy->getList();
$count = count($list['content']);
echo date('[H:i:s Y/m/d]', $end);
echo $echo = $time = "~".round(($end-$start)/60)." m  count $count";
mail("bpteam22@gmail.com", "ALERT update proxy $time", $echo);