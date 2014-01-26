<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 12.05.13
 * Time: 19:05
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Обновление всех списков прокси
 */
use GetContent\old_cProxy as cProxy;
$start = time();
echo date('[H:i:s Y/m/d]', $start);
require_once dirname(__FILE__)."/../../include.php";
$proxy= new old_cProxy();
$proxy->updateAllProxyList(true);
$end = time();
$list = $proxy->selectProxyList($proxy->getDefaultListName());
$count = count($list['content']);
echo date('[H:i:s Y/m/d]', $end);
echo $echo = $time = "~".round(($end-$start)/60)." m  count $count";
mail("bpteam22@gmail.com", "ALERT update proxy $time", $echo);