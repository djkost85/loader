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

$urlSource = "http://gatherproxy.com/subscribe/login";
$nameSource = "gatherproxy.com";
$proxyGatherproxyProxy = array();
$curl = new cSingleCurl();
$curl->setDefaultOption(CURLOPT_REFERER, 'http://gatherproxy.com/subscribe/login');
$curl->setTypeContent("html");
$curl->setDefaultOption(CURLOPT_POSTFIELDS, 'Username=zking.nothingz@gmail.com&Password=)VQd$x;7');
$answerGatherproxy = $curl->load($urlSource);
if (!preg_match('%<a\s*href="(?<url>[^"]+)">Download\s*fully\s*\d+\s*proxies</a>%ims', $answerGatherproxy, $match)) {
	return $proxyGatherproxyProxy;
}
$curl->setDefaultOption(CURLOPT_REFERER, 'http://gatherproxy.com/subscribe/infos');
$answerGatherproxy = $curl->load('http://gatherproxy.com' . $match['url']);
if (!preg_match_all("#(?<ip>\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})\s*)#ims", $answerGatherproxy, $matchesGatherproxy)) return array();
$tmpArray["source"][$nameSource] = true;
$tmpArray["protocol"]['http'] = true;
foreach ($matchesGatherproxy['ip'] as $valueGatherproxy) {
	$tmpArray['proxy'] = trim($valueGatherproxy);
	$proxyGatherproxyProxy['content'][$tmpArray['proxy']] = $tmpArray;
}
unset($curl, $answerGatherproxy);
return is_array($proxyGatherproxyProxy) ? $proxyGatherproxyProxy : array();