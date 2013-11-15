<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 14.05.13
 * Time: 2:36
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
namespace gatherproxy;
use GetContent\c_get_content as c_get_content;

$url_source="http://gatherproxy.com/subscribe/login";
$name_source="gatherproxy.com";
$proxy_gatherproxy_proxy=array();
$get_gatherproxy_content= new c_get_content();
$get_gatherproxy_content->set_default_setting(CURLOPT_REFERER,'http://gatherproxy.com/subscribe/login');
$get_gatherproxy_content->set_type_content("html");
$get_gatherproxy_content->set_default_setting(CURLOPT_POSTFIELDS,'Username=zking.nothingz@gmail.com&Password=)VQd$x;7');
$answer_gatherproxy=$get_gatherproxy_content->get_content($url_source);
if(!preg_match('%<a\s*href="(?<url>[^"]+)">Download\s*fully\s*\d+\s*proxies</a>%ims',$answer_gatherproxy,$match)){
	return $proxy_gatherproxy_proxy;
}
$get_gatherproxy_content->set_default_setting(CURLOPT_REFERER,'http://gatherproxy.com/subscribe/infos');
$answer_gatherproxy=$get_gatherproxy_content->get_content('http://gatherproxy.com'.$match['url']);
if(!preg_match_all("#(?<ip>\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})\s*)#ims",$answer_gatherproxy,$matches_gatherproxy)) return array();
foreach ($matches_gatherproxy['ip'] as $value_gatherproxy)
{
	$tmp_array['proxy']=trim($value_gatherproxy);
	$tmp_array["source_proxy"]=$name_source;
	$tmp_array["type_proxy"]='http';
	$proxy_gatherproxy_proxy['content'][]=$tmp_array;
}
unset($get_gatherproxy_content, $answer_gatherproxy);
return is_array($proxy_gatherproxy_proxy) ? $proxy_gatherproxy_proxy : array();
