<?
use get_content\c_proxy as c_proxy;
set_time_limit(1200);
$start = time();
echo date('[H:i:s Y/m/d]', $start);
require_once dirname(__FILE__)."/../../include.php";
$proxy= new c_proxy();
var_dump($proxy->get_server_ip());
$end = time();
echo date('[H:i:s Y/m/d]', $end);
echo '[~'.(($end-$start)/60).' min]';