<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ec
 * Date: 26.09.13
 * Time: 22:15
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
namespace notan;
use get_content\c_get_content\c_get_content as c_get_content;
use get_content\c_string_work\c_string_work as c_string_work;

$name_source="notan.h1.ru";
$proxy_notan_proxy=array();
for($i=1;$i<=10;$i++){
    $url_source="http://notan.h1.ru/hack/xwww/proxy".$i.".html";
    $get_notan_content= new c_get_content();
    $get_notan_content->set_type_content("html");
    $answer_notan=$get_notan_content->get_content($url_source);
    if(!$answer_notan) return $proxy_notan_proxy;
    if(!$answer_notan=c_string_work::between_tag($answer_notan,'<table border="0" cellspacing="1" width="100%">')) return $proxy_notan_proxy;
    if(!preg_match_all('%<td\s*class=name>\s*(?<ip>\d+\.\d+\.\d+\.\d+\:\d+)\s*</td>%imsu',$answer_notan,$matches_notan)) return $proxy_notan_proxy;
    foreach ($matches_notan['ip'] as $value_notan)
    {
        $tmp_array['proxy']=trim($value_notan);
        $tmp_array["source_proxy"]=$name_source;
        $tmp_array["type_proxy"]='http';
        $proxy_notan_proxy['content'][]=$tmp_array;
    }
}

return $proxy_notan_proxy;
