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
$getContent = new cGetContent();
$getContent->setModeGetContent('multi');// Режим single
$getContent->setTypeContent('text'); // Ожидаемый контент html страница
$getContent->setEncodingAnswer(true);
$getContent->setEncodingName("UTF-8");// если кодировка контента и необходимая кодировка одинаковые то не происходит декодирования
$url[]='http://bpteam.net/encoding_test/cp1251.txt';
$url[]='http://bpteam.net/encoding_test/utf-8.txt';
$getContent->getContent($url);
$answer=$getContent->getAnswer();
/*
 * $_answer[0] Строка декодирована из cp1251 в utf-8
 * $_answer[1] Строка не декодирована utf-8
 */
$getContent->setEncodingAnswer(false);
$getContent->getContent($url);
$answer=$getContent->getAnswer();
/*
 * $_answer[0] Строка не декодирована cp1251
 * $_answer[1] Строка не декодирована utf-8
 */

$getContent->setEncodingAnswer(true);
$getContent->setEncodingName("cp1251");
$getContent->getContent($url);
$answer=$getContent->getAnswer();
/*
 * $_answer[0] Строка не декодирована cp1251
 * $_answer[1] Строка декодирована из utf-8 в cp1251
 */