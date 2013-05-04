<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 03.05.13
 * Time: 12:15
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
include_once "include.php";

$get_content = new c_get_content();
$string_work = new c_string_work();
$proxy = new c_proxy();
$get_content->function_check();
$string_work->function_check();
$proxy->function_chek();