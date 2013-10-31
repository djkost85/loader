<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 04.07.13
 * Time: 15:00
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
namespace twofreeproxy;
use get_content\c_get_content as c_get_content;

$url_source="http://2freeproxy.com/wp-content/plugins/proxy/load_proxy.php";
$name_source="2freeproxy.com";
$proxy_twofreeproxy_proxy = array();
$get_twofreeproxy_content= new c_get_content();
$get_twofreeproxy_content->set_type_content("text");
$http_head=array(
'Host: 2freeproxy.com',
'User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:21.0) Gecko/20100101 Firefox/21.0',
'Accept: application/json, text/javascript, */*; q=0.01',
'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
'X-Requested-With: XMLHttpRequest',
'Referer: http://2freeproxy.com/anonymous-proxy.html',
'Content-Length: 14',
'Connection: keep-alive',
'Pragma: no-cache',
'Cache-Control: no-cache'
);
$get_twofreeproxy_content->set_default_setting(CURLOPT_HTTPHEADER,$http_head);
$get_twofreeproxy_content->set_default_setting(CURLOPT_REFERER,'http://2freeproxy.com/anonymous-proxy.html');
$get_twofreeproxy_content->set_default_setting(CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; rv:21.0) Gecko/20100101 Firefox/21.0');
$get_twofreeproxy_content->set_default_setting(CURLOPT_POST,true);
$get_twofreeproxy_content->set_default_setting(CURLOPT_POSTFIELDS,'type=anonymous');
$answer_twofreeproxy=$get_twofreeproxy_content->get_content($url_source);
$tmp_proxy_array=array();
if($answer_twofreeproxy)
{
	$tmp_json_proxy=json_decode($answer_twofreeproxy,true);
	$tmp_proxy_array=explode('<br>',$tmp_json_proxy['proxy']);
}
$http_head=array(
	'Host: 2freeproxy.com',
	'User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:21.0) Gecko/20100101 Firefox/21.0',
	'Accept: application/json, text/javascript, */*; q=0.01',
	'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
	'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
	'X-Requested-With: XMLHttpRequest',
	'Referer: http://2freeproxy.com/elite-proxy.html',
	'Content-Length: 10',
	'Connection: keep-alive',
	'Pragma: no-cache',
	'Cache-Control: no-cache'
);
$get_twofreeproxy_content->set_default_setting(CURLOPT_HTTPHEADER,$http_head);
$get_twofreeproxy_content->set_default_setting(CURLOPT_REFERER,'http://2freeproxy.com/elite-proxy.html');
$get_twofreeproxy_content->set_default_setting(CURLOPT_POST,true);
$get_twofreeproxy_content->set_default_setting(CURLOPT_POSTFIELDS,'type=elite');
$answer_twofreeproxy=$get_twofreeproxy_content->get_content($url_source);
$tmp_proxy_array2=array();
if($answer_twofreeproxy)
{
	$tmp_json_proxy=json_decode($answer_twofreeproxy,true);
	$tmp_proxy_array2=explode('<br>',$tmp_json_proxy['proxy']);
}
$tmp_proxy_new=array_merge($tmp_proxy_array2,$tmp_proxy_array);
foreach ($tmp_proxy_new as $value_poststar)
{
	$tmp_array['proxy']=trim($value_poststar);
	$tmp_array["source_proxy"]=$name_source;
	$tmp_array["type_proxy"]='http';
	$proxy_twofreeproxy_proxy['content'][]=$tmp_array;
}
return is_array($proxy_twofreeproxy_proxy) ? $proxy_twofreeproxy_proxy : array() ;
