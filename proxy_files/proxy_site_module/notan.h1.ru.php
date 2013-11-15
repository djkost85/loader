<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ec
 * Date: 26.09.13
 * Time: 22:15
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
namespace notan;
use GetContent\c_get_content as c_get_content;

$name_source="notan.h1.ru";
$proxy_notan_proxy=array();
for($i=1;$i<=10;$i++){
	$url_source="http://notan.h1.ru/hack/xwww/proxy".$i.".html";
	$get_notan_content= new c_get_content();
	$get_notan_content->set_type_content("html");
	$answer_notan=$get_notan_content->get_content($url_source);
	if(!$answer_notan) return $proxy_notan_proxy;
	if(!preg_match_all('%<TD\s*class=name>\s*(?<ip>\d+\.\d+\.\d+\.\d+\:\d+)\s*</TD>%ims',$answer_notan,$matches_notan)) return $proxy_notan_proxy;
	foreach ($matches_notan['ip'] as $value_notan)
	{
	$tmp_array['proxy']=trim($value_notan);
	$tmp_array["source_proxy"]=$name_source;
	$tmp_array["type_proxy"]='http';
	$proxy_notan_proxy['content'][]=$tmp_array;
	}
	sleep(rand(1,3));
}
unset($name_source, $get_notan_content, $answer_notan, $matches_notan);
return is_array($proxy_notan_proxy) ? $proxy_notan_proxy : array();
