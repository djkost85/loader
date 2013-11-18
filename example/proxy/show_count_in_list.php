<?php
/**
 * Created by PhpStorm.
 * User: ec
 * Date: 06.11.13
 * Time: 20:00
 * Project: bezagenta.lg.ua
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
use GetContent\cProxy as cProxy;
require_once dirname(__FILE__)."/../../include.php";
$proxy = new cProxy();
foreach ($proxy->getAllNameProxyList() as $name) {
	$proxy->selectProxyList($name);
	$list = $proxy->getProxyList();
	echo $name . '=>' .count($list['content']) . "<br/>\n";
}
