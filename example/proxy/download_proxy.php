<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 11.05.13
 * Time: 14:20
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Загрузка всех прокси из бесплатных источников в список по умолчанию из которого черпают прокси другие листы
 */
use get_content\c_proxy\c_proxy as c_proxy;
set_time_limit(1200);
require_once dirname(__FILE__)."/../../include.php";
$proxy= new c_proxy();
$proxy->update_default_proxy_list(true);//Принудительное обновление основного списка прокси адресов