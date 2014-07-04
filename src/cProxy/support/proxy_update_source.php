<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 31.01.14
 * Time: 13:02
 * Email: bpteam22@gmail.com
 */

require_once dirname(__FILE__)."/../../../include.php";

use GetContent\cUpdateProxy as cUpdateProxy;
register_shutdown_function('sendMessage');
$start = time();
echo date('[H:i:s Y/m/d]', $start);
$proxy= new cUpdateProxy();
foreach($proxy->getAllSiteSourceName() as $source){
	$cmd = 'php -f ' . $proxy->getDirSiteSource() . DIRECTORY_SEPARATOR . $source . '.php > /dev/null &';
	exec($cmd);
}
$end = time();
$text = "\n";
echo date('[H:i:s Y/m/d]', $end);
$time = round(($end-$start)/60);
echo $text = $time." m  $text";

function sendMessage(){
	global $text;
	mail("zking.nothingz@gmail.com", "update proxy source", $text);
}