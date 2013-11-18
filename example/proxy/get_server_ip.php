<?
use GetContent\cProxy as cProxy;
set_time_limit(1200);
$start = time();
echo date('[H:i:s Y/m/d]', $start);
require_once dirname(__FILE__)."/../../include.php";
$proxy= new cProxy();
var_dump($proxy->getServerIp());
$end = time();
echo date('[H:i:s Y/m/d]', $end);
echo '[~'.(($end-$start)/60).' min]';