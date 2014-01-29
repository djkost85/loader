<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 14.05.13
 * Time: 3:35
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

use GetContent\cSingleCurl as cSingleCurl;
use GetContent\cStringWork as cStringWork;

return array();
$urlSource = "http://spys.ru/aproxy/";
$nameSource = "spys.ru";
$tmpArray["source"][$nameSource] = true;
$tmpArray["protocol"]['http'] = true;
$curl = new cSingleCurl();
$curl->setTypeContent("html");
$curl->setDefaultOption(CURLOPT_POST, true);
$curl->setDefaultOption(CURLOPT_POSTFIELDS, 'sto=%CF%EE%EA%E0%E7%E0%F2%FC+200');
$answerSpys = $curl->getContent($urlSource);
if (!$answerSpys) return array();
if (!$answerSpys = cStringWork::betweenTag($answerSpys, '<table width="100%" BORDER=0 CELLPADDING=1 CELLSPACING=1>')) return array();
if (!preg_match_all("#(?<ip>\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})\s*)#ims", $answerSpys, $matchesSpys)) return array();
foreach ($matchesSpys['ip'] as $valueSpys) {
	$tmpArray['proxy'] = trim($valueSpys);
	$proxySpysProxy['content'][$tmpArray['proxy']] = $tmpArray;
}
unset($answerSpys, $curl);
return is_array($proxySpysProxy) ? $proxySpysProxy : array();
