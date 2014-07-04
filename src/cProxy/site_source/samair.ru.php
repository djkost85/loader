<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ec
 * Date: 26.09.13
 * Time: 22:15
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

use GetContent\cSingleCurl as cSingleCurl;
use GetContent\cStringWork as cStringWork;
use GetContent\cUpdateProxy as cUpdateProxy;

return array();
$urlSource = "http://www.samair.ru/proxy/proxy-01.htm";
$nameSource = "samair.ru";
$curl = new cSingleCurl();
$updateProxy = new cUpdateProxy();
$curl->setTypeContent("text");
$proxySamair = array();
do {
	$answerSamair = $curl->load($urlSource);
	if (!$answerSamair) break;
	if (!preg_match('%<script\s*src="(?<jsFile>/js/\d+.js)"\s*type="text/javascript"></script>%imsu', $answerSamair, $jsFile)) break;
	$answerJs = $curl->load('http://www.samair.ru' . $jsFile);
	if (!preg_match_all('%<tr\s*class="[^"]*"\s*rel="\d*">(?U)(?<proxyHtml>.*)</tr>%imsu', $answerSamair, $matchesHtml)) break;
	foreach ($matchesHtml['proxyHtml'] as $proxyHtml) {
		if (cStringWork::isIp($proxyAddress)) {
			$proxySamair[] = trim($proxyAddress);
		}
	}
	if (preg_match('%<a\s*class="page"\s*href="(?<next>proxy\-\d+.htm)">next</a>%imsu', $answerSamair, $matchNext)) {
		$urlSource = "http://hidemyass.com" . $matchNext['next'];
	} else {
		unset($urlSource);
	}
	sleep(rand(1, 3));
} while (isset($urlSource));
$updateProxy->saveSource($nameSource, $proxySamair);
return $proxySamair;
