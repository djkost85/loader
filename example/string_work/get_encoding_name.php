<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 11.05.13
 * Time: 13:21
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Тест работы функции определения кодировки (Только для кирилицы)
 */
use GetContent\cStringWork as c_string_work;
require_once dirname(__FILE__)."/../../include.php";
$cp1251=file_get_contents('cp1251.txt');
$utf8=file_get_contents('utf-8.txt');
$name=cStringWork::get_encoding_name($cp1251); // $name=windows-1251
$name=cStringWork::get_encoding_name($utf8); // $name=UTF-8