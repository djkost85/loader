<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ec
 * Date: 25.09.13
 * Time: 22:25
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace hidemyass;
use get_content\c_get_content as c_get_content;
use get_content\c_string_work as c_string_work;
//return array();
$url_source="http://hidemyass.com/proxy-list/";
$name_source="hidemyass.com";
$get_hidemyass_content= new c_get_content();
$get_hidemyass_content->set_type_content("html");
$proxy_hidemyass = array();
do{
	$answer_hidemyass=$get_hidemyass_content->get_content($url_source);
	if(!$answer_hidemyass) return $proxy_hidemyass;
	if(preg_match_all('%<tr\s*class="[^"]*"\s*rel="\d*">(?U)(?<proxy_html>.*)</tr>%imsu',$answer_hidemyass,$matches_html)){
	foreach ($matches_html['proxy_html'] as $proxy_html) {
	    preg_match_all('%\.(?<class>[\w_-]+){display\:\s*inline\s*}%imsu',$proxy_html,$matches_class);
	    $need_class = implode('|',$matches_class['class']);
	    preg_match_all('%(<(span|div)\s*(style\s*=\s*"\s*display\s*\:\s*inline\s*"|class\s*=\s*"(\d+|'.$need_class.')")\s*>\s*([^<>]+)\s*|</(span|div|style)>\s*([^"<>]+)\s*)%imsu',$proxy_html,$matches_proxy);
	    preg_match('%</td>\s*<td>\s*(?<port>\d+)\s*</td>%imsu',$proxy_html,$match_port);
	    $proxy_address = implode('',$matches_proxy[0]).':'.$match_port['port'];
	    $proxy_address = preg_replace('%<[^<>]*>%imsu','',$proxy_address);
	    $proxy_address = preg_replace('%\s+%ms','',$proxy_address);
	    if(c_string_work::is_ip($proxy_address))
	    {
	        $tmp_array['proxy'] = trim($proxy_address);
	        $tmp_array["source_proxy"] = $name_source;
	        $tmp_array["type_proxy"] = 'http';
	        $proxy_hidemyass['content'][] = $tmp_array;
	    }
	}
	}
	if(preg_match('%<a\s*href="(?<next>[^"]+)"\s*class="next">Next%imsu',$answer_hidemyass,$match_next)){
	    $url_source = "http://hidemyass.com".$match_next['next'];
	} else {
	    unset($url_source);
	}
	sleep(rand(1,3));
}while(isset($url_source));
unset($get_hidemyass_content, $answer_hidemyass);
return $proxy_hidemyass;