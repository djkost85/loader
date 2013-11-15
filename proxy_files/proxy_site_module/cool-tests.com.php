<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 14.05.13
 * Time: 3:35
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
namespace cool_tests;
use GetContent\cGetContent as c_get_content;
//return array();
$url_source="http://www.cool-tests.com/all-working-proxies.php";
$name_source="cool-tests.com";
$get_cool_tests_content= new cGetContent();
$get_cool_tests_content->set_encoding_answer(true);
$get_cool_tests_content->set_encoding_name('UTF-8');
$get_cool_tests_content->get_content('http://www.cool-tests.com');
$get_cool_tests_content->set_type_content("html");
$get_cool_tests_content->set_default_setting(CURLOPT_REFERER,'http://www.cool-tests.com');
$answer_cool_tests=$get_cool_tests_content->get_content($url_source);
$proxy_cool_tests_proxy = array();
if(!$answer_cool_tests) return array();
if(!preg_match_all("#(?<ip>\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})#imsu",$answer_cool_tests,$matches_cool_tests)) return array();
foreach ($matches_cool_tests['ip'] as $value_cool_tests)
{
	$tmp_array['proxy']=trim($value_cool_tests);
	$tmp_array["source_proxy"]=$name_source;
	$tmp_array["type_proxy"]='http';
	$proxy_cool_tests_proxy['content'][]=$tmp_array;
}
unset($url_source, $name_source, $get_cool_tests_content, $answer_cool_tests, $matches_cool_tests, $value_cool_tests);
return is_array($proxy_cool_tests_proxy) ? $proxy_cool_tests_proxy : array();
