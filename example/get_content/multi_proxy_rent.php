<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 13.05.13
 * Time: 18:01
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Проверка работы запросов с арендой прокси адресов
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
$getContent1->proxy->setMethodGetProxy('rent');//Включаем метод получения прокси random

$url2[]='http://bpteam.net';
$url2[]='http://radio-t.com';
$getContent2=new cGetContent();
$getContent2->setUseProxy(true); //Включаем работу через прокси
$getContent2->proxy->selectProxyList('all'); // Выбираем список прокси
$getContent2->proxy->setMethodGetProxy('rent');//Включаем метод получения прокси random
$getContent2->setCountMultiStream(2); // Количество запросов к одному url

$getContent1->getContent($url1);
$getContent2->getContent($url2);
$getContent1->getContent($url1);
$getContent2->getContent($url2);
$getContent1->getContent($url2);
$getContent2->getContent($url1);