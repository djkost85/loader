<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 10.05.13
 * Time: 19:09
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Получение данных в режиме single
 */
use get_content\c_get_content as c_get_content;
require_once dirname(__FILE__)."/../../include.php";
set_time_limit(600);
$get_content = new c_get_content();
$get_content->set_mode_get_content('single');// Режим single
//$get_content->set_type_content('html'); // Ожидаемый контент html страница
$url='http://market.yandex.ua/model.xml?modelid=10495456&hid=91491';
$get_content->setCookieFile('test');
$answer=$get_content->get_content($url);
var_dump($answer);
/*
 * $answert содержимое http://bpteam.net
 */