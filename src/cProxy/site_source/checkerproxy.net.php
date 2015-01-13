<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 14.05.13
 * Time: 2:36
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

require_once __DIR__."/../../../include.php";
use GetContent\cSingleCurl as cSingleCurl;
use GetContent\cStringWork as cStringWork;
use GetContent\cUpdateProxy as cUpdateProxy;

return array();
$urlSource = "http://checkerproxy.net/all_proxy";
$nameSource = "checkerproxy.net";
$curl = new cSingleCurl();
$updateProxy = new cUpdateProxy();
$curl->setTypeContent("text");
$answerCheckerProxy = $curl->load($urlSource);
$proxyCheckerProxy = array();
if(preg_match_all('#(?<ip>\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})\s*)#ims', $answerCheckerProxy, $matchesCheckerProxy)){
	foreach ($matchesCheckerProxy['ip'] as $valueCheckerProxy) {
		$valueCheckerProxy = trim($valueCheckerProxy);
		if(cStringWork::isIp($valueCheckerProxy)){
			$proxyCheckerProxy[] = $valueCheckerProxy;
		}
	}
}
$updateProxy->saveSource($nameSource, $proxyCheckerProxy);
return $proxyCheckerProxy;