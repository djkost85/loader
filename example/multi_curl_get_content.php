<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 31.01.14
 * Time: 15:19
 * Email: bpteam22@gmail.com
 */

require_once __DIR__ . "/../include.php";

use GetContent\cMultiCurl as cMultiCurl;


$curl = new cMultiCurl();
$curl->load(array('http://ya.ru', 'http://google.com'));
var_dump($curl->getAnswer()); // array('page of ya.ru', 'page of google.com')