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
use GetContent\cGetContent as cGetContent;
require_once dirname(__FILE__)."/../../include.php";
set_time_limit(600);
$getContent = new cGetContent();
$getContent->setModeGetContent('multi'); // Режим multi
$getContent->setCountMultiStream(2); // количество запросов к одному url, подразумевается использование для работы с прокси, на случай если прокси не вренет результат, то другой вернет, и выберается самый большой результат
$getContent->setTypeContent('html'); // Ожидаемый контент html страница
$url[]="http://ya.ru";
$url[]="http://vk.com";
$url[]="http://google.com";
$url[]="http://bpteam.net";
$getContent->getContent($url);
$answer=$getContent->getAnswer();
var_dump($answer);
/*
 * $_answer[0] содержимое http://ya.ru
 * $_answer[1] содержимое http://vk.com
 * $_answer[2] содержимое http://google.com
 * $_answer[3] содержимое http://bpteam.net
 */
$answer=$getContent->getAnswer(true);
/*
 * $_answer[0][0] содержимое http://ya.ru
 * $_answer[0][1] содержимое http://ya.ru
 * $_answer[1][0] содержимое http://vk.com
 * $_answer[1][1] содержимое http://vk.com
 * $_answer[2][0] содержимое http://google.com
 * $_answer[2][1] содержимое http://google.com
 * $_answer[3][0] содержимое http://bpteam.net
 * $_answer[3][1] содержимое http://bpteam.net
 */