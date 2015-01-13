<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 14.05.13
 * Time: 3:35
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

require_once __DIR__."/../../../include.php";
use GetContent\cSingleCurl as cSingleCurl;
use GetContent\cStringWork as cStringWork;
use GetContent\cUpdateProxy as cUpdateProxy;

return array();
$urlSource = "http://spys.ru/aproxy/";
$nameSource = "spys.ru";
$curl = new cSingleCurl();
$updateProxy = new cUpdateProxy();
$curl->setTypeContent("html");
$curl->setDefaultOption(CURLOPT_POST, true);
$curl->setDefaultOption(CURLOPT_POSTFIELDS, 'sto=%CF%EE%EA%E0%E7%E0%F2%FC+200');
$answerSpys = $curl->load($urlSource);
$answerSpys = cStringWork::betweenTag($answerSpys, '<table width="100%" BORDER=0 CELLPADDING=1 CELLSPACING=1>');
if (preg_match_all('#(?<ip>\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})\s*)#ims', $answerSpys, $matchesSpys)){
	foreach ($matchesSpys['ip'] as $valueSpys) {
		$proxySpysProxy[] = trim($valueSpys);
	}
}
$updateProxy->saveSource($nameSource, $proxySpysProxy);
return $proxySpysProxy;