<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ec
 * Date: 26.09.13
 * Time: 22:15
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
namespace samair;

use GetContent\cGetContent as cGetContent;
use GetContent\cStringWork as cStringWork;

return array();
$urlSource = "http://www.samair.ru/proxy/proxy-01.htm";
$nameSource = "samair.ru";
$getSamairContent = new cGetContent();
$getSamairContent->setTypeContent("text");
$proxySamair = array();
$tmpArray["source_proxy"] = $nameSource;
$tmpArray["type_proxy"] = 'http';
do {
	$answerSamair = $getSamairContent->getContent($urlSource);
	if (!$answerSamair) return $proxySamair;
	if (!preg_match('%<script\s*src="(?<jsFile>/js/\d+.js)"\s*type="text/javascript"></script>%imsu', $answerSamair, $jsFile)) break;
	$answerJs = $getSamairContent->getContent('http://www.samair.ru' . $jsFile);
	if (!preg_match_all('%<tr\s*class="[^"]*"\s*rel="\d*">(?U)(?<proxyHtml>.*)</tr>%imsu', $answerSamair, $matchesHtml)) break;
	foreach ($matchesHtml['proxyHtml'] as $proxyHtml) {
		if (cStringWork::isIp($proxyAddress)) {
			$tmpArray['proxy'] = trim($proxyAddress);
			$proxySamair['content'][] = $tmpArray;
		}
	}

	if (preg_match('%<a\s*class="page"\s*href="(?<next>proxy\-\d+.htm)">next</a>%imsu', $answerSamair, $matchNext)) {
		$urlSource = "http://hidemyass.com" . $matchNext['next'];
	} else {
		unset($urlSource);
	}
	sleep(rand(1, 3));
} while (isset($urlSource));
unset($answerSamair, $getSamairContent);
return is_array($proxySamair) ? $proxySamair : array();
