<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 31.01.14
 * Time: 14:16
 * Email: bpteam22@gmail.com
 */
require_once __DIR__ . "/../include.php";

use GetContent\cSingleCurl as cSingleCurl;


$curl = new cSingleCurl();
$curl->load('http://ya.ru');
$answer = $curl->getAnswer(); // page of ya.ru