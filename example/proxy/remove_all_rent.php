<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 13.05.13
 * Time: 22:40
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Удаление из списка всех аренд
 */
use get_content\c_proxy as c_proxy;
require_once dirname(__FILE__)."/../../include.php";
$c_proxy = new c_proxy();
$c_proxy->select_proxy_list('bpteam');
$c_proxy->remove_all_rent();