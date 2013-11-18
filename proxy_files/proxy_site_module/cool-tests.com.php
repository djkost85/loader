<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 14.05.13
 * Time: 3:35
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
namespace cool_tests;

use GetContent\cGetContent as cGetContent;

//return array();
$urlSource = "http://www.cool-tests.com/all-working-proxies.php";
$nameSource = "cool-tests.com";
$tmpArray["source_proxy"] = $nameSource;
$tmpArray["type_proxy"] = 'http';
$getCoolTestsContent = new cGetContent();
$getCoolTestsContent->setEncodingAnswer(true);
$getCoolTestsContent->setEncodingName('UTF-8');
$getCoolTestsContent->getContent('http://www.cool-tests.com');
$getCoolTestsContent->setTypeContent("html");
$getCoolTestsContent->setDefaultSetting(CURLOPT_REFERER, 'http://www.cool-tests.com');
$answerCoolTests = $getCoolTestsContent->getContent($urlSource);
$proxyCoolTestsProxy = array();
if (!$answerCoolTests) return array();
if (!preg_match_all("#(?<ip>\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})#imsu", $answerCoolTests, $matchesCoolTests)) return array();
foreach ($matchesCoolTests['ip'] as $valueCoolTests) {
	$tmpArray['proxy'] = trim($valueCoolTests);
	$proxyCoolTestsProxy['content'][] = $tmpArray;
}
unset($urlSource, $nameSource, $getCoolTestsContent, $answerCoolTests, $matchesCoolTests, $valueCoolTests);
return is_array($proxyCoolTestsProxy) ? $proxyCoolTestsProxy : array();
