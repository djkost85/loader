<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 14.05.13
 * Time: 2:36
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

use GetContent\cSingleCurl as cSingleCurl;
use GetContent\cStringWork as cStringWork;

$urlSource = "http://www.poststar.ru/proxy.htm";
$nameSource = "poststar.ru";
$curl = new cSingleCurl();
$curl->setTypeContent("html");
$tmpArray["source_proxy"] = $nameSource;
$tmpArray["type_proxy"] = 'http';
$answerPoststar = $curl->getContent($urlSource);
if (!$answerPoststar) return array();
if (!$answerPoststar = cStringWork::betweenTag($answerPoststar, '<table width="730" border="0" align="center">')) {
	return array();
}
if (!preg_match_all("#(?<ip>\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})\s*)#ims", $answerPoststar, $matchesPoststar)) {
	return array();
}
$proxyPoststarProxy = array();
foreach ($matchesPoststar['ip'] as $valuePoststar) {
	$tmpArray['proxy'] = trim($valuePoststar);
	$proxyPoststarProxy['content'][] = $tmpArray;
}
unset($curl, $answerPoststar);
return is_array($proxyPoststarProxy) ? $proxyPoststarProxy : array();
