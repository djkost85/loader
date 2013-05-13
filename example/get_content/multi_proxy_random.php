<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 12.05.13
 * Time: 1:03
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Получение контента в режиме multi через прокси список без аренды
 */
use get_content\c_get_content\c_get_content as c_get_content;
require_once "../../include.php";
set_time_limit(0);
$get_content=new c_get_content();
$get_content->set_use_proxy(true); //Включаем работу через прокси
$get_content->proxy->select_proxy_list('bpteam'); // Выбираем список прокси
$get_content->proxy->set_method_get_proxy('random');//Включаем метод получения прокси random
$url[]="http://bpteam.net";
$url[]="http://ya.ru";
$answer=$get_content->get_content($url); // посылаем запрос через прокси из списка bpteam
