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
use GetContent\cStringWork as cStringWork;
require_once dirname(__FILE__)."/../../include.php";
$cp1251=file_get_contents('cp1251.txt');
$utf8=file_get_contents('utf-8.txt');
$name=cStringWork::getEncodingName($cp1251); // $name=windows-1251
$name=cStringWork::getEncodingName($utf8); // $name=UTF-8