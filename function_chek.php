<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 03.05.13
 * Time: 12:15
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
use get_content\c_get_content as c_get_content;
use get_content\c_proxy as c_proxy;
use get_content\c_string_work as c_string_work;
include_once dirname(__FILE__)."/include.php";

$get_content = new c_get_content();
$string_work = new c_string_work();
$proxy = new c_proxy();
$get_content->function_check();
$string_work->function_check();
$proxy->function_chek();