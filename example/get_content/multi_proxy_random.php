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

$url1[]='http://ya.ru';
$url1[]='http://habrahabr.ru';
$url1[]='http://google.com';
$getContent1=new cGetContent();
$getContent1->setUseProxy(true); //Включаем работу через прокси
$getContent1->proxy->selectProxyList('all'); // Выбираем список прокси
$getContent1->proxy->setMethodGetProxy('random');//Включаем метод получения прокси random

$url2[]='http://bpteam.net';
$url2[]='http://radio-t.com';
$getContent2=new cGetContent();
$getContent2->setUseProxy(true); //Включаем работу через прокси
$getContent2->proxy->selectProxyList('all'); // Выбираем список прокси
$getContent2->proxy->setMethodGetProxy('random');//Включаем метод получения прокси random
$getContent2->setCountMultiStream(2); // Количество запросов к одному url

$answer=$getContent1->getContent($url1);
$answer=$getContent2->getContent($url2);
$answer=$getContent1->getContent($url1);
$answer=$getContent2->getContent($url2);
$answer=$getContent1->getContent($url2);
$answer=$getContent2->getContent($url1);