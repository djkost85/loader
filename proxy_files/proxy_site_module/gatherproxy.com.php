<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 14.05.13
 * Time: 2:36
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
namespace gatherproxy;

use GetContent\cGetContent as cGetContent;

$urlSource = "http://gatherproxy.com/subscribe/login";
$nameSource = "gatherproxy.com";
$proxyGatherproxyProxy = array();
$getGatherproxyContent = new cGetContent();
$getGatherproxyContent->setDefaultSetting(CURLOPT_REFERER, 'http://gatherproxy.com/subscribe/login');
$getGatherproxyContent->setTypeContent("html");
$getGatherproxyContent->setDefaultSetting(CURLOPT_POSTFIELDS, 'Username=zking.nothingz@gmail.com&Password=)VQd$x;7');
$answerGatherproxy = $getGatherproxyContent->getContent($urlSource);
if (!preg_match('%<a\s*href="(?<url>[^"]+)">Download\s*fully\s*\d+\s*proxies</a>%ims', $answerGatherproxy, $match)) {
	return $proxyGatherproxyProxy;
}
$getGatherproxyContent->setDefaultSetting(CURLOPT_REFERER, 'http://gatherproxy.com/subscribe/infos');
$answerGatherproxy = $getGatherproxyContent->getContent('http://gatherproxy.com' . $match['url']);
if (!preg_match_all("#(?<ip>\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})\s*)#ims", $answerGatherproxy, $matchesGatherproxy)) return array();
$tmpArray["source_proxy"] = $nameSource;
$tmpArray["type_proxy"] = 'http';
foreach ($matchesGatherproxy['ip'] as $valueGatherproxy) {
	$tmpArray['proxy'] = trim($valueGatherproxy);
	$proxyGatherproxyProxy['content'][] = $tmpArray;
}
unset($getGatherproxyContent, $answerGatherproxy);
return is_array($proxyGatherproxyProxy) ? $proxyGatherproxyProxy : array();
