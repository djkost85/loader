<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 04.07.13
 * Time: 15:00
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * @info Передать в Get параметр t имя тестируемого модуля.
 */
use GetContent\cProxy as c_proxy;
$start = time();
echo date('[H:i:s Y/m/d]', $start);
require_once dirname(__FILE__)."/../../include.php";
set_time_limit(3600);
$proxy= new cProxy();
$test_module = isset($_GET['t']) ? $_GET['t'] : 'cool';
$data=$proxy->test_download_proxy($test_module);
$end = time();
echo date('[H:i:s Y/m/d]', $end);
echo '[~'.(($end-$start)/60).' min]';
echo '[count:'.count($data['content']).']';
var_dump($data);