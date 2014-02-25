<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 14.05.13
 * Time: 3:35
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

use GetContent\cSingleCurl as cSingleCurl;

$urlSource = "http://www.cool-tests.com/all-working-proxies.php";
$nameSource = "cool-tests.com";
$tmpArray["source"][$nameSource] = true;
$tmpArray["protocol"]['http'] = true;
$curl = new cSingleCurl();
$curl->setEncodingAnswer(true);
$curl->setEncodingName('UTF-8');
$curl->load('http://www.cool-tests.com');
$curl->setTypeContent("html");
$curl->setDefaultOption(CURLOPT_REFERER, 'http://www.cool-tests.com');
$answerCoolTests = $curl->load($urlSource);
$proxyCoolTestsProxy = array();
if (!$answerCoolTests) return array();
if (!preg_match_all("#(?<ip>\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})#imsu", $answerCoolTests, $matchesCoolTests)) return array();
foreach ($matchesCoolTests['ip'] as $valueCoolTests) {
	$tmpArray['proxy'] = trim($valueCoolTests);
	$proxyCoolTestsProxy['content'][$tmpArray['proxy']] = $tmpArray;
}
unset($urlSource, $nameSource, $curl, $answerCoolTests, $matchesCoolTests, $valueCoolTests);
return is_array($proxyCoolTestsProxy) ? $proxyCoolTestsProxy : array();
