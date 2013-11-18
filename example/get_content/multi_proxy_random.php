<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 12.05.13
 * Time: 1:03
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Получение контента в режиме multi через прокси список без аренды
 */
use GetContent\cGetContent as cGetContent;
require_once dirname(__FILE__)."/../../include.php";
set_time_limit(0);

$url_1[]='http://ya.ru';
$url_1[]='http://habrahabr.ru';
$url_1[]='http://google.com';
$get_content_1=new cGetContent();
$get_content_1->setUseProxy(true); //Включаем работу через прокси
$get_content_1->proxy->selectProxyList('all'); // Выбираем список прокси
$get_content_1->proxy->setMethodGetProxy('random');//Включаем метод получения прокси random

$url_2[]='http://bpteam.net';
$url_2[]='http://radio-t.com';
$get_content_2=new cGetContent();
$get_content_2->setUseProxy(true); //Включаем работу через прокси
$get_content_2->proxy->selectProxyList('all'); // Выбираем список прокси
$get_content_2->proxy->setMethodGetProxy('random');//Включаем метод получения прокси random
$get_content_2->setCountMultiStream(2); // Количество запросов к одному url

$answer=$get_content_1->getContent($url_1);
$answer=$get_content_2->getContent($url_2);
$answer=$get_content_1->getContent($url_1);
$answer=$get_content_2->getContent($url_2);
$answer=$get_content_1->getContent($url_2);
$answer=$get_content_2->getContent($url_1);