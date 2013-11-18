<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 12.05.13
 * Time: 1:08
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Удаление файлов с cookie которым больше часа
 */
use GetContent\cGetContent as cGetContent;
require_once dirname(__FILE__)."/../../include.php";
$get_content=new cGetContent();
$get_content->clearCookie(600);