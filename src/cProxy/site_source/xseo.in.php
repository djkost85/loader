<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 14.05.13
 * Time: 3:26
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

use GetContent\cSingleCurl as cSingleCurl;
use GetContent\cStringWork as cStringWork;

$urlSource="http://xseo.in/freeproxy";
$nameSource="xseo.in";
$curl = new cSingleCurl();
$curl->setTypeContent("html");
$curl->setDefaultOption(CURLOPT_POST,true);
$curl->setDefaultOption(CURLOPT_POSTFIELDS,'submit=%CF%EE%EA%E0%E7%E0%F2%FC+%EF%EE+100+%EF%F0%EE%EA%F1%E8+%ED%E0+%F1%F2%F0%E0%ED%E8%F6%E5');
$answerXseo=$curl->load($urlSource);
if(!$answerXseo) return array();
if(!$answerXseo=cStringWork::betweenTag($answerXseo,'<table width="100%" BORDER=0 CELLPADDING=0 CELLSPACING=1>',false)) return array();
if(!preg_match_all("#(?<ip>\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})\s*)#ims",$answerXseo,$matchesXseo)) return array();
$proxyXseoProxy=array();
$tmpArray["source"][$nameSource] = true;
$tmpArray["protocol"]['http'] = true;
foreach ($matchesXseo['ip'] as $value_xseo){
	$tmpArray['proxy'] = trim($value_xseo);
	$proxyXseoProxy['content'][$tmpArray['proxy']] = $tmpArray;
}
unset($getXseoContent, $answerXseo);
return is_array($proxyXseoProxy) ? $proxyXseoProxy : array();