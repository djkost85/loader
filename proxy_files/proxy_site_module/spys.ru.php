<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 14.05.13
 * Time: 3:35
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
namespace spys;

use GetContent\cGetContent as cGetContent;
use GetContent\cStringWork as cStringWork;

return array();
$urlSource = "http://spys.ru/aproxy/";
$nameSource = "spys.ru";
$tmpArray["source_proxy"] = $nameSource;
$tmpArray["type_proxy"] = 'http';
$getSpysContent = new cGetContent();
$getSpysContent->setTypeContent("html");
$getSpysContent->setDefaultSetting(CURLOPT_POST, true);
$getSpysContent->setDefaultSetting(CURLOPT_POSTFIELDS, 'sto=%CF%EE%EA%E0%E7%E0%F2%FC+200');
$answerSpys = $getSpysContent->get_content($urlSource);
if (!$answerSpys) return array();
if (!$answerSpys = cStringWork::between_tag($answerSpys, '<table width="100%" BORDER=0 CELLPADDING=1 CELLSPACING=1>')) return array();
if (!preg_match_all("#(?<ip>\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})\s*)#ims", $answerSpys, $matchesSpys)) return array();
foreach ($matchesSpys['ip'] as $valueSpys) {
	$tmpArray['proxy'] = trim($valueSpys);
	$proxySpysProxy['content'][] = $tmpArray;
}
unset($answerSpys, $getSpysContent);
return is_array($proxySpysProxy) ? $proxySpysProxy : array();
