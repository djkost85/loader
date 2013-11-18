<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 11.05.13
 * Time: 14:27
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Создание , изменение, обновление и удаление прокси листа
 */
use GetContent\cProxy as cProxy;
require_once dirname(__FILE__)."/../../include.php";
$proxy= new cProxy();
$proxy->createProxyList('test',"http://ya.ru",array("#yandex#ims"),array('cookie'=> 1,'referer' => 1, 'country' => 'United States'),false); // создаем список с именем test
$proxy->configProxyList('test',"http://bpteam.net",array("#\+380632359213#ims",'#bpteam22@gmail.com#ims'),array('anonym' => 1,'cookie' => 1,'referer' => 1),true);//Меняем требования к прокси
$proxy->updateProxyList('test',true);// Обновление листа с добавлением актуальных прокси
$proxy->deleteProxyList('test');// Удаление прокси листа