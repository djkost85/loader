<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ec
 * Date: 25.09.13
 * Time: 22:25
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace foxtools;
use get_content\c_get_content as c_get_content;
use get_content\c_string_work as c_string_work;
//return array();
$url_source="http://foxtools.ru/Proxy?page=";
$name_source="foxtools.ru";
$get_foxtools_content= new c_get_content();
$get_foxtools_content->set_type_content("html");
$proxy_foxtools = array();
for($nom=1;$nom<50;$nom++){
	$url_page = $url_source.$nom;
	$answer_foxtools=$get_foxtools_content->get_content($url_page);
	if(!$answer_foxtools) return $proxy_foxtools;
	$answer_foxtools = c_string_work::between_tag($answer_foxtools,'<table style="width:100%" id="theProxyList">');
	if(!preg_match_all('%<td\s*style="[^"]*">(?<ip>\d+.\d+.\d+.\d+)</td>\s*<td\s*style="[^"]*">(?<port>\d+)</td>%imsu',$answer_foxtools,$matches_ip))    break;
	foreach ($matches_ip['ip'] as $key => $proxy_ip) {
	$proxy_address = $proxy_ip.':'.$matches_ip['port'][$key];
	if(c_string_work::is_ip($proxy_address))
	{
	    $tmp_array['proxy'] = trim($proxy_address);
	    $tmp_array["source_proxy"] = $name_source;
	    $tmp_array["type_proxy"] = 'http';
	    $proxy_foxtools['content'][] = $tmp_array;
	}
	}
	sleep(rand(1,3));
}
unset($url_source, $name_source, $get_foxtools_content, $url_page, $answer_foxtools, $matches_ip);
return $proxy_foxtools;