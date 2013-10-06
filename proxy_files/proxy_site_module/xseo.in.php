<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 14.05.13
 * Time: 3:26
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
namespace xseo;
use get_content\c_get_content\c_get_content as c_get_content;
use get_content\c_string_work\c_string_work as c_string_work;

$url_source="http://xseo.in/freeproxy";
$name_source="xseo.in";
$get_xseo_content= new c_get_content();
$get_xseo_content->set_type_content("html");
$get_xseo_content->set_default_setting(CURLOPT_POST,true);
$get_xseo_content->set_default_setting(CURLOPT_POSTFIELDS,'submit=%CF%EE%EA%E0%E7%E0%F2%FC+%EF%EE+100+%EF%F0%EE%EA%F1%E8+%ED%E0+%F1%F2%F0%E0%ED%E8%F6%E5');
$answer_xseo=$get_xseo_content->get_content($url_source);
if(!$answer_xseo) return array();
if(!$answer_xseo=c_string_work::between_tag($answer_xseo,'<table width="100%" BORDER=0 CELLPADDING=0 CELLSPACING=1>',false)) return array();
if(!preg_match_all("#(?<ip>\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})\s*)#ims",$answer_xseo,$matches_xseo)) return array();
$proxy_xseo_proxy=array();
foreach ($matches_xseo['ip'] as $value_xseo)
{
    $tmp_array['proxy']=trim($value_xseo);
    $tmp_array["source_proxy"]=$name_source;
    $tmp_array["type_proxy"]='http';
    $proxy_xseo_proxy['content'][]=$tmp_array;
}
unset($get_xseo_content, $answer_xseo);
return $proxy_xseo_proxy;
