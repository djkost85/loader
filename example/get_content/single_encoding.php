<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 10.05.13
 * Time: 22:47
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Декодирование в режиме single
 */
use GetContent\cGetContent as cGetContent;
require_once dirname(__FILE__)."/../../include.php";
set_time_limit(600);
$getContent = new cGetContent();
$getContent->setModeGetContent('single');// Режим single
$getContent->setTypeContent('text'); // Ожидаемый контент html страница
$getContent->setEncodingAnswer(true);
$getContent->setEncodingName("UTF-8");
$url='http://bpteam.net/encoding_test/cp1251.txt'; // Кодировка страницы на день публикации(05/13/2013) windows-1251
$answer=$getContent->getContent($url);
echo $answer;
$getContent->setEncodingAnswer(false);
$url='http://bpteam.net/encoding_test/cp1251.txt'; // Кодировка страницы на день публикации(05/13/2013) windows-1251
$answer=$getContent->getContent($url);
echo $answer;
$url='http://bpteam.net/encoding_test/utf-8.txt'; // Кодировка страницы на день публикации(05/13/2013) utf-8
$answer=$getContent->getContent($url);
echo $answer;
$getContent->setEncodingAnswer(true);
$getContent->setEncodingName("UTF-8"); // если кодировка контента и необходимая кодировка одинаковые то не происходит декодирования
$url='http://bpteam.net/encoding_test/utf-8.txt'; // Кодировка страницы на день публикации(05/13/2013) utf-8
$answer=$getContent->getContent($url);
echo $answer;
$getContent->setEncodingAnswer(true);
$getContent->setEncodingName("cp1251"); // если кодировка контента и необходимая кодировка одинаковые то не происходит декодирования
$url='http://bpteam.net/encoding_test/cp1251.txt'; // Кодировка страницы на день публикации(05/13/2013) windows-1251
$answer=$getContent->getContent($url);
echo $answer;