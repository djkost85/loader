<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 31.01.14
 * Time: 13:02
 * Email: bpteam22@gmail.com
 */

require_once dirname(__FILE__) . "/../include.php";

use GetContent\cUpdateProxy as cUpdateProxy;

$proxy = new cUpdateProxy('http://free-lance.dyndns.info/proxy_check.php');
$proxy->updateAllList(true);