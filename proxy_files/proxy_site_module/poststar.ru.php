<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 14.05.13
 * Time: 2:36
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
namespace poststar;
use GetContent\c_get_content as c_get_content;
use GetContent\c_string_work as c_string_work;

$url_source="http://www.poststar.ru/proxy.htm";
$name_source="poststar.ru";
$get_poststar_content= new c_get_content();
$get_poststar_content->set_type_content("html");
$answer_poststar=$get_poststar_content->get_content($url_source);
if(!$answer_poststar) return array();
if(!$answer_poststar=c_string_work::between_tag($answer_poststar,'<table width="730" border="0" align="center">')) return array();
if(!preg_match_all("#(?<ip>\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})\s*)#ims",$answer_poststar,$matches_poststar)) return array();
$proxy_poststar_proxy=array();
foreach ($matches_poststar['ip'] as $value_poststar)
{
	$tmp_array['proxy']=trim($value_poststar);
	$tmp_array["source_proxy"]=$name_source;
	$tmp_array["type_proxy"]='http';
	$proxy_poststar_proxy['content'][]=$tmp_array;
}
unset($get_poststar_content, $answer_poststar);
return is_array($proxy_poststar_proxy)? $proxy_poststar_proxy : array();
