<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 14.05.13
 * Time: 3:35
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
namespace stopinfection;
use get_content\c_get_content\c_get_content as c_get_content;
//return array();
$url_source="http://stopinfection.narod.ru/Proxy.htm";
$name_source="cool-tests.com";
$get_stopinfection_content= new c_get_content();
$get_stopinfection_content->set_encoding_answer(true);
$get_stopinfection_content->set_encoding_name('UTF-8');
$get_stopinfection_content->set_type_content("html");
$answer_stopinfection=$get_stopinfection_content->get_content($url_source);
$proxy_stopinfection_proxy = array();
if(!$answer_stopinfection) return array();
if(!preg_match_all('#(?<ip>\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})#imsu',$answer_stopinfection,$matches_stopinfection)) return array();
foreach ($matches_stopinfection['ip'] as $value_stopinfection)
{
    $tmp_array['proxy']=trim($value_stopinfection);
    $tmp_array["source_proxy"]=$name_source;
    $tmp_array["type_proxy"]='http';
    $proxy_stopinfection_proxy['content'][]=$tmp_array;
}
unset($url_source, $name_source, $get_stopinfection_content, $answer_stopinfection, $matches_stopinfection, $value_stopinfection);
return $proxy_stopinfection_proxy;