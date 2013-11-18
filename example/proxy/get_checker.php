<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 14.11.13
 * Time: 11:43
 * Email: bpteam22@gmail.com
 */
use GetContent\cProxy as cProxy;
set_time_limit(1200);
$start = time();
echo date('[H:i:s Y/m/d]', $start);
require_once dirname(__FILE__)."/../../include.php";
$proxy= new cProxy();
echo $checker = $proxy->getProxyChecker();
$end = time();
echo date('[H:i:s Y/m/d]', $end);
echo '[~'.(($end-$start)/60).' min]';