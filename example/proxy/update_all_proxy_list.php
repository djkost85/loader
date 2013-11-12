<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 12.05.13
 * Time: 19:05
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Обновление всех списков прокси
 */
use get_content\c_proxy as c_proxy;
$start = time();
echo date('[H:i:s Y/m/d]', $start);
require_once dirname(__FILE__)."/../../include.php";
$proxy= new c_proxy();
$proxy->update_all_proxy_list(true);
$end = time();
$list = $proxy->select_proxy_list($proxy->get_default_list_name());
$count = count($list['content']);
echo date('[H:i:s Y/m/d]', $end);
echo $echo = $time = "~".round(($end-$start)/60)." m  count $count";
mail("bpteam22@gmail.com", "ALERT update proxy $time", $echo);