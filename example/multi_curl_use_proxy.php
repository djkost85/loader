<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 03.02.14
 * Time: 10:41
 * Email: bpteam22@gmail.com
 */

require_once dirname(__FILE__) . "/../include.php";

use GetContent\cMultiCurl as cMultiCurl;

$curl = new cMultiCurl();

$curl->setUseProxy('178.21.14.55:8080'); // use this proxy 178.21.14.55:8080

$curl->setUseProxy(true); // use cProxy
$curl->proxy->selectList('all');