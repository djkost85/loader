<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 11.05.13
 * Time: 14:20
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Загрузка всех прокси из бесплатных источников в список по умолчанию из которого черпают прокси другие листы
 */
use GetContent\cProxy as cProxy;
set_time_limit(1200);
require_once dirname(__FILE__)."/../../include.php";
$proxy= new cProxy();
$proxy->updateDefaultProxyList(true);//Принудительное обновление основного списка прокси адресов