<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 10.05.13
 * Time: 22:47
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Декодирование в режиме single
 */
use get_content\c_get_content\c_get_content as c_get_content;
//use get_content\c_proxy\c_proxy as c_proxy;
use get_content\c_string_work\c_string_work as c_string_work;
require_once dirname(__FILE__)."/../../include.php";
set_time_limit(600);
$get_content = new c_get_content();
$get_content->set_mode_get_content('single');// Режим single
$get_content->set_type_content('text'); // Ожидаемый контент html страница
$get_content->set_encoding_answer(true);
$get_content->set_encoding_name("UTF-8");
$url='http://bpteam.net/encoding_test/cp1251.txt'; // Кодировка страницы на день публикации(05/13/2013) windows-1251
$answer=$get_content->get_content($url);
echo $answer;
$get_content->set_encoding_answer(false);
$url='http://bpteam.net/encoding_test/cp1251.txt'; // Кодировка страницы на день публикации(05/13/2013) windows-1251
$answer=$get_content->get_content($url);
echo $answer;
$url='http://bpteam.net/encoding_test/utf-8.txt'; // Кодировка страницы на день публикации(05/13/2013) utf-8
$answer=$get_content->get_content($url);
echo $answer;
$get_content->set_encoding_answer(true);
$get_content->set_encoding_name("UTF-8"); // если кодировка контента и необходимая кодировка одинаковые то не происходит декодирования
$url='http://bpteam.net/encoding_test/utf-8.txt'; // Кодировка страницы на день публикации(05/13/2013) utf-8
$answer=$get_content->get_content($url);
echo $answer;
$get_content->set_encoding_answer(true);
$get_content->set_encoding_name("cp1251"); // если кодировка контента и необходимая кодировка одинаковые то не происходит декодирования
$url='http://bpteam.net/encoding_test/cp1251.txt'; // Кодировка страницы на день публикации(05/13/2013) windows-1251
$answer=$get_content->get_content($url);
echo $answer;