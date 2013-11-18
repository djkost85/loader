<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 11.05.13
 * Time: 0:14
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Проверка декодирования в режиме multi
 */
use GetContent\cGetContent as cGetContent;
require_once dirname(__FILE__)."/../../include.php";
set_time_limit(600);
$get_content = new cGetContent();
$get_content->setModeGetContent('multi');// Режим single
$get_content->setTypeContent('text'); // Ожидаемый контент html страница
$get_content->setEncodingAnswer(true);
$get_content->setEncodingName("UTF-8");// если кодировка контента и необходимая кодировка одинаковые то не происходит декодирования
$url[]='http://bpteam.net/encoding_test/cp1251.txt';
$url[]='http://bpteam.net/encoding_test/utf-8.txt';
$get_content->getContent($url);
$answer=$get_content->getAnswer();
/*
 * $_answer[0] Строка декодирована из cp1251 в utf-8
 * $_answer[1] Строка не декодирована utf-8
 */
$get_content->setEncodingAnswer(false);
$get_content->getContent($url);
$answer=$get_content->getAnswer();
/*
 * $_answer[0] Строка не декодирована cp1251
 * $_answer[1] Строка не декодирована utf-8
 */

$get_content->setEncodingAnswer(true);
$get_content->setEncodingName("cp1251");
$get_content->getContent($url);
$answer=$get_content->getAnswer();
/*
 * $_answer[0] Строка не декодирована cp1251
 * $_answer[1] Строка декодирована из utf-8 в cp1251
 */