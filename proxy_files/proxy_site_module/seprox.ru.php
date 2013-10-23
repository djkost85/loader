<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 08.05.13
 * Time: 5:33
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Модуль к классу  c_proxy для скачивания списка прокси адресов с сайта seprox.ru
 */
namespace seprox;
use get_content\c_get_content as c_get_content;
use get_content\c_string_work as c_string_work;
//return array();
$url_source="http://seprox.ru/ru/proxy_filter/0_0_0_0_0_0_0_0_0_";
$name_source="seprox.ru";
//return array();
$get_seprox_content= new c_get_content();
$get_seprox_content->set_type_content("html");
$pagenation=0;
$content=$get_seprox_content->get_content($url_source.$pagenation.".html");
if(!$content) return array();
if(preg_match("/<div\s*class=\"countResult\">\s*Всего\s*найдено.\s*(\d+)\s*<\/div>/iUs", $content, $match)) $count_page=ceil($match[1]/15);
else return false;
// JavaScript приколы с приведением типов. Расшифровка:
$javascript_encode = array(
	"a"=>"(![]+[])[+!+[]]",
	"b"=>"([]+[]+{})[!+[]+!+[]]",
	"c"=>"([![]]+{})[+!+[]+[+[]]]",
	"d"=>"([]+[]+[][[]])[!+[]+!+[]]",
	"e"=>"(!![]+[])[!+[]+!+[]+!+[]]",
	"f"=>"(![]+[])[+[]]",
	"i"=>"([![]]+[][[]])[+!+[]+[+[]]]",
	"n"=>"([]+[]+[][[]])[+!+[]]",
	"o"=>"([]+[]+{})[+!+[]]",
	"r"=>"(!![]+[])[+!+[]]",
	"t"=>"(!![]+[])[+[]]",
	"u"=>"(!![]+[])[!+[]+!+[]]",
	" "=>"(+{}+[]+[]+[]+[]+{})[+!+[]+[+[]]]",
	"***"=>"+++",
	""=>"+",
	"+"=>"***"
);
$proxy_seprox=array();
do {
	$reg_ex='#<tr\s*class="proxyStr">\s*<td>\s*<script\s*type="text/javascript">\s*(?<js>[^<]*)\s*</script>\s*</td>\s*<td>\s*(?<type_proxy>.*)\s*</td>#iUms';
	if(!preg_match_all($reg_ex, $content, $matches_secret_code)) break;
	foreach ($matches_secret_code['js'] as $key_secret_code => $str_secret_code)
	{
	if(!preg_match('#Proxy=String.fromCharCode\((?<js_code>[^\)]*)\)#iUs', $str_secret_code,$match_secret_array)) break;
	$lit=explode(",",$match_secret_array['js_code']);
	$litera=array();
	foreach ($lit as $key => $value) $litera[$key]=chr($value);
	foreach ($litera as $key_litera => $value_litera)
	    $str_secret_code=preg_replace('#Proxy\['.$key_litera.'\]#iUs',$value_litera, $str_secret_code);
	foreach ($javascript_encode as $key_javascript => $value_javascript)
	    $str_secret_code=preg_replace('#'.preg_quote($value_javascript,'#').'#',$key_javascript,$str_secret_code);
	preg_match_all('#(?:\(|\+)(?<ip>\w+)#s', $str_secret_code,$matches_secret_var);
	$ip="";
	foreach ($matches_secret_var['ip'] as $key_ip => $value_ip)
	    if(preg_match('#'.$value_ip.'=\'(?<ip>[^\']*)\'#s', $str_secret_code, $match_ip)) $ip.=$match_ip['ip'];
	if(c_string_work::is_ip($ip))
	{
	    $tmp_array['proxy']=trim($ip);
	    $tmp_array["source_proxy"]=$name_source;
	    $tmp_array["type_proxy"]=trim($matches_secret_code['type_proxy'][$key_secret_code]);
	    $proxy_seprox['content'][]=$tmp_array;
	}
	}
	$pagenation++;
	sleep(rand(1,3));
	if(!$content=$get_seprox_content->get_content($url_source.$pagenation.".html")) continue;
}while($pagenation<$count_page);
unset($javascript_encode);
unset($matches_secret_code);
unset($str_secret_code);
unset($get_seprox_content);
unset($content);
return $proxy_seprox;