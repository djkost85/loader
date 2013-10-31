<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ec
 * Date: 26.09.13
 * Time: 22:15
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
namespace samair;
use get_content\c_get_content as c_get_content;
use get_content\c_string_work as c_string_work;
return array();
$url_source="http://www.samair.ru/proxy/proxy-01.htm";
$name_source="samair.ru";
$get_samair_content= new c_get_content();
$get_samair_content->set_type_content("text");
$proxy_samair = array();
do{
	$answer_samair = $get_samair_content->get_content($url_source);
	return $answer_samair;
	if(!$answer_samair) return $proxy_samair;
	if(!preg_match('%<script\s*src="(?<js_file>/js/\d+.js)"\s*type="text/javascript"></script>%imsu', $answer_samair, $js_file)) break;
	$answer_js = $get_samair_content->get_content('http://www.samair.ru'.$js_file);
//-----------------------------------------
	if(!preg_match_all('%<tr\s*class="[^"]*"\s*rel="\d*">(?U)(?<proxy_html>.*)</tr>%imsu',$answer_samair,$matches_html)) break;
	foreach ($matches_html['proxy_html'] as $proxy_html) {
	if(c_string_work::is_ip($proxy_address))
	{
	    $tmp_array['proxy'] = trim($proxy_address);
	    $tmp_array["source_proxy"] = $name_source;
	    $tmp_array["type_proxy"] = 'http';
	    $proxy_samair['content'][] = $tmp_array;
	}
	}

	if(preg_match('%<a\s*class="page"\s*href="(?<next>proxy\-\d+.htm)">next</a>%imsu',$answer_samair,$match_next)){
	$url_source = "http://hidemyass.com".$match_next['next'];
	} else {
	unset($url_source);
	}
	sleep(rand(1,3));
}while(isset($url_source));
unset($answer_samair, $get_samair_content);
return is_array($proxy_samair)? $proxy_samair : array();