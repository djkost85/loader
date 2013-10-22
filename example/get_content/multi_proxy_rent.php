<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 13.05.13
 * Time: 18:01
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Проверка работы запросов с арендой прокси адресов
 */
use get_content\c_get_content as c_get_content;
require_once dirname(__FILE__)."/../../include.php";
set_time_limit(0);
$url_1[]='http://ya.ru';
$url_1[]='http://habrahabr.ru';
$url_1[]='http://google.com';
$get_content_1=new c_get_content();
$get_content_1->set_use_proxy(true); //Включаем работу через прокси
$get_content_1->proxy->select_proxy_list('all'); // Выбираем список прокси
$get_content_1->proxy->set_method_get_proxy('rent');//Включаем метод получения прокси random

$url_2[]='http://bpteam.net';
$url_2[]='http://radio-t.com';
$get_content_2=new c_get_content();
$get_content_2->set_use_proxy(true); //Включаем работу через прокси
$get_content_2->proxy->select_proxy_list('all'); // Выбираем список прокси
$get_content_2->proxy->set_method_get_proxy('rent');//Включаем метод получения прокси random
$get_content_2->set_count_multi_stream(2); // Количество запросов к одному url

$get_content_1->get_content($url_1);
$get_content_2->get_content($url_2);
$get_content_1->get_content($url_1);
$get_content_2->get_content($url_2);
$get_content_1->get_content($url_2);
$get_content_2->get_content($url_1);