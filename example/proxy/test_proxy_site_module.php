<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 04.07.13
 * Time: 15:00
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
use get_content\c_proxy\c_proxy as c_proxy;
require_once dirname(__FILE__)."/../../include.php";
set_time_limit(3600);
$proxy= new c_proxy();
$data=$proxy->test_download_proxy('cool');
var_dump($data);