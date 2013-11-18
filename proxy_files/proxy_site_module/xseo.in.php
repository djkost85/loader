<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 14.05.13
 * Time: 3:26
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
namespace xseo;
use GetContent\cGetContent as cGetContent;
use GetContent\cStringWork as cStringWork;

$urlSource="http://xseo.in/freeproxy";
$nameSource="xseo.in";
$getXseoContent= new cGetContent();
$getXseoContent->setTypeContent("html");
$getXseoContent->setDefaultSetting(CURLOPT_POST,true);
$getXseoContent->setDefaultSetting(CURLOPT_POSTFIELDS,'submit=%CF%EE%EA%E0%E7%E0%F2%FC+%EF%EE+100+%EF%F0%EE%EA%F1%E8+%ED%E0+%F1%F2%F0%E0%ED%E8%F6%E5');
$answerXseo=$getXseoContent->getContent($urlSource);
if(!$answerXseo) return array();
if(!$answerXseo=cStringWork::betweenTag($answerXseo,'<table width="100%" BORDER=0 CELLPADDING=0 CELLSPACING=1>',false)) return array();
if(!preg_match_all("#(?<ip>\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})\s*)#ims",$answerXseo,$matchesXseo)) return array();
$proxyXseoProxy=array();
$tmpArray["source_proxy"]=$nameSource;
$tmpArray["type_proxy"]='http';
foreach ($matchesXseo['ip'] as $value_xseo)
{
	$tmpArray['proxy']=trim($value_xseo);
	$proxyXseoProxy['content'][]=$tmpArray;
}
unset($getXseoContent, $answerXseo);
return is_array($proxyXseoProxy) ? $proxyXseoProxy : array();
