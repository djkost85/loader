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
use GetContent\cUpdateProxy as cUpdateProxy;

$urlSource="http://xseo.in/freeproxy";
$nameSource="xseo.in";
$curl = new cSingleCurl();
$updateProxy = new cUpdateProxy();
$curl->setTypeContent("html");
$curl->setDefaultOption(CURLOPT_POST,true);
$curl->setDefaultOption(CURLOPT_POSTFIELDS,'submit=%CF%EE%EA%E0%E7%E0%F2%FC+%EF%EE+100+%EF%F0%EE%EA%F1%E8+%ED%E0+%F1%F2%F0%E0%ED%E8%F6%E5');
$answerXseo=$curl->load($urlSource);
$proxyXseoProxy=array();
$answerXseo=cStringWork::betweenTag($answerXseo,'<table width="100%" BORDER=0 CELLPADDING=0 CELLSPACING=1>',false);
if(preg_match_all('#(?<ip>\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})\s*)#ims',$answerXseo,$matchesXseo)){
	foreach ($matchesXseo['ip'] as $value_xseo){
		$proxyXseoProxy[] = trim($value_xseo);
	}
}
$updateProxy->saveSource($nameSource, $proxyXseoProxy);
return $proxyXseoProxy;