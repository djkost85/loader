<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 12.05.13
 * Time: 19:05
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Обновление всех списков прокси
 */
use get_content\c_proxy\c_proxy as c_proxy;
require_once "../../include.php";
set_time_limit(3600);
$proxy= new c_proxy();
$proxy->update_all_proxy_list();