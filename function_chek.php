<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 03.05.13
 * Time: 12:15
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
use GetContent\cGetContent as c_get_content;
use GetContent\cProxy as c_proxy;
use GetContent\c_string_work as c_string_work;
include_once dirname(__FILE__)."/include.php";

$get_content = new cGetContent();
$string_work = new c_string_work();
$proxy = new cProxy();
$get_content->function_check();
$string_work->function_check();
$proxy->function_chek();