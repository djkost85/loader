<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 03.05.13
 * Time: 12:15
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
use GetContent\cGetContent as cGetContent;
use GetContent\old_cProxy as cProxy;
use GetContent\cStringWork as cStringWork;
include_once dirname(__FILE__)."/include.php";

$get_content = new cGetContent();
$string_work = new cStringWork();
$proxy = new old_cProxy();
$get_content->functionCheck();
$string_work->functionCheck();
$proxy->functionChek();