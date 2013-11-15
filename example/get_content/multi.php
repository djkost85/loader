<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 10.05.13
 * Time: 13:48
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Получение данных с нескольких url в режиме multi
 */
use GetContent\c_get_content as c_get_content;
require_once dirname(__FILE__)."/../../include.php";
set_time_limit(600);
$get_content = new c_get_content();
$get_content->set_mode_get_content('multi'); // Режим multi
$get_content->set_count_multi_stream(2); // количество запросов к одному url, подразумевается использование для работы с прокси, на случай если прокси не вренет результат, то другой вернет, и выберается самый большой результат
$get_content->set_type_content('html'); // Ожидаемый контент html страница
$url[]="http://ya.ru";
$url[]="http://vk.com";
$url[]="http://google.com";
$url[]="http://bpteam.net";
$get_content->get_content($url);
$answer=$get_content->get_answer();
var_dump($answer);
/*
 * $answer[0] содержимое http://ya.ru
 * $answer[1] содержимое http://vk.com
 * $answer[2] содержимое http://google.com
 * $answer[3] содержимое http://bpteam.net
 */
$answer=$get_content->get_answer(true);
/*
 * $answer[0][0] содержимое http://ya.ru
 * $answer[0][1] содержимое http://ya.ru
 * $answer[1][0] содержимое http://vk.com
 * $answer[1][1] содержимое http://vk.com
 * $answer[2][0] содержимое http://google.com
 * $answer[2][1] содержимое http://google.com
 * $answer[3][0] содержимое http://bpteam.net
 * $answer[3][1] содержимое http://bpteam.net
 */