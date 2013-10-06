<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 14.05.13
 * Time: 3:35
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
namespace spys;
use get_content\c_get_content\c_get_content as c_get_content;
use get_content\c_string_work\c_string_work as c_string_work;
return array();
$url_source="http://spys.ru/aproxy/";
$name_source="spys.ru";
$get_spys_content= new c_get_content();
$get_spys_content->set_type_content("html");
$get_spys_content->set_default_setting(CURLOPT_POST,true);
$get_spys_content->set_default_setting(CURLOPT_POSTFIELDS,'sto=%CF%EE%EA%E0%E7%E0%F2%FC+200');
$answer_spys=$get_spys_content->get_content($url_source);
if(!$answer_spys) return array();
if(!$answer_spys=c_string_work::between_tag($answer_spys,'<table width="100%" BORDER=0 CELLPADDING=1 CELLSPACING=1>')) return array();
if(!preg_match_all("#(?<ip>\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})\s*)#ims",$answer_spys,$matches_spys)) return array();
foreach ($matches_spys['ip'] as $value_spys)
{
    $tmp_array['proxy']=trim($value_spys);
    $tmp_array["source_proxy"]=$name_source;
    $tmp_array["type_proxy"]='http';
    $proxy_spys_proxy['content'][]=$tmp_array;
}
unset($answer_spys, $get_spys_content);
return $proxy_spys_proxy;