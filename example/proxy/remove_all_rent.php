<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 13.05.13
 * Time: 22:40
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Удаление из списка всех аренд
 */
use GetContent\cProxy as cProxy;
require_once dirname(__FILE__)."/../../include.php";
$c_proxy = new cProxy();
$c_proxy->selectProxyList('bpteam');
$c_proxy->removeAllRent();