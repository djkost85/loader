<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 03.02.14
 * Time: 10:42
 * Email: bpteam22@gmail.com
 */

require_once __DIR__ . "/../include.php";

use GetContent\cPhantomJS as cPhantomJS;

$phantomJS = new cPhantomJS(PHANTOMJS_EXE);

$phantomJS->setOption('proxy', '172.21.12.54:8080'); // use proxy 172.21.12.54:8080
$phantomJS->setOption('proxy-type', ' http'); //  http|socks5

// if need use auth in proxy
$phantomJS->setOption('proxy-auth', 'username:password');