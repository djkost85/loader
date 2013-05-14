<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 14.05.13
 * Time: 3:11
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
namespace hideme;
use get_content\c_get_content\c_get_content as c_get_content;
use get_content\c_string_work\c_string_work as c_string_work;
return array();
$url_source="http://hideme.ru/proxy-list/?type=h";
$name_source="hideme.ru";
$get_hideme_content= new c_get_content();
$get_hideme_content->set_type_content("html");
$answer_hideme=$get_hideme_content->get_content($url_source);
if(!$answer_hideme) return array();
if(!$answer_hideme=c_string_work::between_tag($answer_hideme,'<table class="pl" cellpadding="0" cellspacing="0">')) return array();
