<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 04.07.13
 * Time: 15:00
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * @info Передать в Get параметр t имя тестируемого модуля.
 */
use get_content\c_proxy as c_proxy;
require_once dirname(__FILE__)."/../../include.php";
set_time_limit(3600);
$proxy= new c_proxy();
$test_module = isset($_GET['t']) ? $_GET['t'] : 'cool';
$data=$proxy->test_download_proxy($test_module);
echo 'count:'.count($data['content']);
var_dump($data);