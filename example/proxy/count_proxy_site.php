<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ec
 * Date: 26.09.13
 * Time: 20:25
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

use GetContent\cProxy as c_proxy;
require_once dirname(__FILE__)."/../../include.php";
set_time_limit(3600);
$proxy = new cProxy();
$name = isset($_GET['l']) ? $_GET['l'] : 'all';
$proxy->select_proxy_list($name);
$list = $proxy->get_proxy_list();

foreach ($list['content'] as $proxy) {
	$source[$proxy['source_proxy']][] = $proxy['proxy'];
	if($proxy['cookie']) $data['cookie'][] = $proxy['proxy'];
	if($proxy['get']) $data['get'][] = $proxy['proxy'];
	if($proxy['post']) $data['post'][] = $proxy['proxy'];
	if($proxy['referer']) $data['referer'][] = $proxy['proxy'];
	if($proxy['anonym']) $data['anonym'][] = $proxy['proxy'];
	$country[$proxy['country']][] = $proxy['proxy'];
}
echo "<p>last update : ".date("H:i:s d-m-Y",$list['time'])."</p>";
echo '<p>------------FUNCTION----------</p>';
arsort($data);
foreach ($data as $source_proxy => $proxyes) {
	echo '<p>'.$source_proxy.':'.count($proxyes).'</p>';
}
echo '<p>------------SOURCE----------</p>';
arsort($source);
foreach ($source as $source_proxy => $proxyes) {
		echo '<p>'.$source_proxy.':'.count($proxyes).'</p>';
}
echo '<p>------------COUNTRY----------</p>';
arsort($country);
foreach ($country as $source_proxy => $proxyes) {
		echo '<p>'.$source_proxy.':'.count($proxyes).'</p>';
}