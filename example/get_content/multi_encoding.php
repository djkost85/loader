<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 11.05.13
 * Time: 0:14
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Проверка декодирования в режиме multi
 */
use get_content\c_get_content as c_get_content;
require_once dirname(__FILE__)."/../../include.php";
set_time_limit(600);
$get_content = new c_get_content();
$get_content->set_mode_get_content('multi');// Режим single
$get_content->set_type_content('text'); // Ожидаемый контент html страница
$get_content->set_encoding_answer(true);
$get_content->set_encoding_name("UTF-8");// если кодировка контента и необходимая кодировка одинаковые то не происходит декодирования
$url[]='http://bpteam.net/encoding_test/cp1251.txt';
$url[]='http://bpteam.net/encoding_test/utf-8.txt';
$get_content->get_content($url);
$answer=$get_content->get_answer();
/*
 * $answer[0] Строка декодирована из cp1251 в utf-8
 * $answer[1] Строка не декодирована utf-8
 */
$get_content->set_encoding_answer(false);
$get_content->get_content($url);
$answer=$get_content->get_answer();
/*
 * $answer[0] Строка не декодирована cp1251
 * $answer[1] Строка не декодирована utf-8
 */

$get_content->set_encoding_answer(true);
$get_content->set_encoding_name("cp1251");
$get_content->get_content($url);
$answer=$get_content->get_answer();
/*
 * $answer[0] Строка не декодирована cp1251
 * $answer[1] Строка декодирована из utf-8 в cp1251
 */