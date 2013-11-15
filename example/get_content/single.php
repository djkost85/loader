<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 10.05.13
 * Time: 19:09
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Получение данных в режиме single
 */
use GetContent\cGetContent as c_get_content;
require_once dirname(__FILE__)."/../../include.php";
set_time_limit(600);
$get_content = new cGetContent();
$get_content->set_mode_get_content('single');// Режим single
//$_getContent->setTypeContent('html'); // Ожидаемый контент html страница
$url='http://market.yandex.ua/model.xml?modelid=10495456&hid=91491';
$get_content->setCookieFile('test');
$answer=$get_content->get_content($url);
var_dump($answer);
/*
 * $answert содержимое http://bpteam.net
 */