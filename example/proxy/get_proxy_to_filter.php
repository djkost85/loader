<?php
/**
 * Created by PhpStorm.
 * User: ec
 * Date: 31.10.13
 * Time: 23:55
 * Project: bezagenta.lg.ua
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
use GetContent\cProxy as c_proxy;
ini_set('display_errors',1);
require_once dirname(__FILE__)."/../../include.php";
set_time_limit(3600);
$proxy = new cProxy();
$name = 'all';
$proxy->select_proxy_list($name);
$list = $proxy->get_proxy_list();
$functionList = array(
	'cookie',
	'get',
	'post',
	'post',
	'referer',
	'anonym',
	'country'
);
if(isset($_GET['filter'])){
	$function = array();
	foreach($functionList as $functionName){
		if(isset($_GET[$functionName]) && $_GET[$functionName]) $function[$functionName] = $_GET[$functionName];
	}
	$proxyList = $proxy->get_proxy_by_function($list['content'],$function);
	foreach($proxyList as $ipProxy){
		$data[] = $ipProxy['proxy'];
	}
	echo implode("\n",$data);
} else {
	$data = array();
	foreach ($list['content'] as $proxyInList) {
		if($proxyInList['cookie']) $data['cookie'][] = $proxyInList['proxy'];
		if($proxyInList['get']) $data['get'][] = $proxyInList['proxy'];
		if($proxyInList['post']) $data['post'][] = $proxyInList['proxy'];
		if($proxyInList['referer']) $data['referer'][] = $proxyInList['proxy'];
		if($proxyInList['anonym']) $data['anonym'][] = $proxyInList['proxy'];
		$country[$proxyInList['country']][] = $proxyInList['proxy'];
	}
	rsort($data['cookie']);
	rsort($data['get']);
	rsort($data['post']);
	rsort($data['referer']);
	rsort($data['anonym']);
	arsort($country);
	?>
<form>
	<input type="checkbox" name="get" value="1"/><label>get[<?=count($data['get'])?>]</label>
	<input type="checkbox" name="cookie" value="1"/><label>cookie[<?=count($data['cookie'])?>]</label>
	<input type="checkbox" name="post" value="1"/><label>post[<?=count($data['post'])?>]</label>
	<input type="checkbox" name="referer" value="1"/><label>referer[<?=count($data['referer'])?>]</label>
	<input type="checkbox" name="anonym" value="1"/><label>anonym[<?=count($data['anonym'])?>]</label>
	<label>country</label>
	<select name="country">
		<option value="">all[<?=count($data['get'])?>]</option>
		<? foreach($country as $countryName => $proxyInCountry){ ?>
		<option value="<?=$countryName?>"><?=$countryName?>[<?=count($proxyInCountry)?>]</option>
		<?}?>
	</select>
	<input type="submit" name="filter" value="filter">
</form>
<?
}
