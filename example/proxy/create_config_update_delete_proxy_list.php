<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 11.05.13
 * Time: 14:27
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Создание , изменение, обновление и удаление прокси листа
 */
use get_content\c_proxy\c_proxy as c_proxy;
require_once dirname(__FILE__)."../../include.php";
$proxy= new c_proxy();
$proxy->create_proxy_list('test',"http://ya.ru",array("#yandex#ims"),array('cookie','referer'),false); // создаем список с именем test
$proxy->config_proxy_list('test',"http://bpteam.net",array("#\+380632359213#ims",'#bpteam22@gmail.com#ims'),array('anonym','cookie','referer'),true);//Меняем требования к прокси
$proxy->update_proxy_list('test',true);// Обновление листа с добавлением актуальных прокси
$proxy->delete_proxy_list('test');// Удаление прокси листа