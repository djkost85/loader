<?php
/**
 * Created by PhpStorm.
 * User: ec
 * Date: 06.11.13
 * Time: 20:00
 * Project: bezagenta.lg.ua
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
use get_content\c_proxy as c_proxy;
require_once dirname(__FILE__)."/../../include.php";
$proxy = new c_proxy();
foreach ($proxy->get_all_name_proxy_list() as $name) {
	$proxy->select_proxy_list($name);
	$list = $proxy->get_proxy_list();
	echo $name . '=>' .count($list['content']) . "<br/>\n";
}
