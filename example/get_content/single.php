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
use get_content\c_get_content\c_get_content as c_get_content;
//use get_content\c_proxy\c_proxy as c_proxy;
//use get_content\c_string_work\c_string_work as c_string_work;
require_once "../../include.php";
set_time_limit(600);
$get_content = new c_get_content();
$get_content->set_mode_get_content('single');// Режим single
$get_content->set_type_content('html'); // Ожидаемый контент html страница
$url='http://bpteam.net';
$answer=$get_content->get_content($url);
/*
 * $answert содержимое http://bpteam.net
 */