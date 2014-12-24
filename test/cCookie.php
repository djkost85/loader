<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 09.01.14
 * Time: 9:21
 * Email: bpteam22@gmail.com
 */

require_once dirname(__FILE__) . '/cnfg_test.php';
use GetContent\cCookie as cCookie;
echo "cCookie<br/>\n";

define('FILE_NAME','testCookies');

$functions = array(
	'open',
	'create',
	'fromCurl',
	'toCurl',
	'toFileCurl',
	'fromFileCurl',
	'toPhantomJS',
	'toFilePhantomJS',
	'fromPhantomJS',
	'fromFilePhantomJS',
	'delete',
);

runTest($functions, 'cCookie_');

function cCookie_open(){
	$cookie = new cCookie();
	$cookie->open(FILE_NAME);
	return file_exists($cookie->getFileCurlName()) && file_exists($cookie->getFilePhantomJSName()) && file_exists($cookie->getFileName());
}

function cCookie_create(){
	$cookie = new cCookie();
	$cookie->open(FILE_NAME);
	$cookie->create('testName', 'testValue', '.test1.ru', '/lib3', date('l, d-M-y H:i:s e', time() + 43200), false, false, true);
	$cookie->create('2testName', '2testValue', '.test1.ru', '/', date('l, d-M-y H:i:s e',63200), true, true, true);
	$cookies = $cookie->getCookies('http://www.test1.ru/blablalba/22');
	return $cookies['testName']['value'] == 'testValue' && $cookies['2testName']['value'] == '2testValue';
}

function cCookie_fromCurl(){
	$curlCookie='# Netscape HTTP Cookie File
# http://curl.haxx.se/rfc/cookie_spec.html
# This file was generated by libcurl! Edit at your own risk.

#HttpOnly_.test1.ru	TRUE	/	FALSE	0	PHPSESSID	57f1bcb516082cbdbc1929331e0e7312
.test1.ru	TRUE	/	FALSE	1416569958	BITRIX_SM_GUEST_ID	87997
.test1.ru	TRUE	/	FALSE	1416569958	BITRIX_SM_LAST_VISIT	26.11.2013+15%3A39%3A18
.test1.ru	TRUE	/	FALSE	1416569702	BITRIX_SM_SALE_UID	753959';
	$testCookie = cCookie::fromCurl($curlCookie);
	return ($testCookie['BITRIX_SM_GUEST_ID']['value'] == '87997' && $testCookie['PHPSESSID']['value'] == '57f1bcb516082cbdbc1929331e0e7312' && $testCookie['PHPSESSID']['httponly']);
}

function cCookie_toCurl(){
	$cookie1 = new cCookie();
	$cookie1->open(FILE_NAME);
	$cookie1->create('testName', 'testValue', '.test1.ru', '/lib3', date('l, d-M-y H:i:s e', time() + 43200), false, false, true);
	$cookie1->create('2testName', '2testValue', '.test1.ru', '/', date('l, d-M-y H:i:s e',63200), true, true, true);

	$cookie = new cCookie();
	$cookie->open(FILE_NAME);
	$testCookie = $cookie->getCookies('test1.ru');
	$testCurlLine = $cookie->toCurl($testCookie['2testName']);
	return $testCurlLine == "#HttpOnly_.test1.ru	TRUE	/	TRUE	63200	2testName	2testValue";
}

function cCookie_toFileCurl(){
	$cookie = new cCookie();
	$cookie->open(FILE_NAME);
	$cookie->create('PHPSESSID', '123456', '.test1.ru', '/', 0, true, false, true);
	$cookie->toFileCurl($cookie->getCookies('test1.ru'));
	$testCookie = $cookie->getCookies('test1.ru');
	return $testCookie['PHPSESSID']['value'] == '123456';
}

function cCookie_fromFileCurl(){
	$cookie1 = new cCookie();
	$cookie1->open(FILE_NAME);
	$cookie1->create('PHPSESSID', '123456', '.test1.ru', '/', 0, true, false, true);
	$cookie1->create('testName', 'testValue', '.test1.ru', '/lib3', date('l, d-M-y H:i:s e', time() + 43200), false, false, true);
	$cookie1->create('2testName', '2testValue', '.test1.ru', '/', date('l, d-M-y H:i:s e',63200), true, true, true);
	$cookie1->toFileCurl($cookie1->getCookies('test1.ru'));

	$cookie = new cCookie();
	$cookie->open(FILE_NAME);
	$cookie->delete('.test1.ru');
	$cookie->creates($cookie->fromFileCurl());
	$testCookie = $cookie->getCookies('test1.ru');
	return ($testCookie['testName']['value'] == 'testValue' && $testCookie['PHPSESSID']['value'] == '123456' && $testCookie['PHPSESSID']['httponly']);
}

function cCookie_toPhantomJS(){
	$cookie = new cCookie();
	$cookie->open(FILE_NAME);
	$testCookie = $cookie->getCookies('test1.ru');
	$testPhantomCookie = $cookie->toPhantomJS($testCookie['2testName']);
	$regEx = '%^2testName=2testValue; expires=[^;]*; secure; HttpOnly; domain=.test1.ru; path=/$%';
	return preg_match($regEx, $testPhantomCookie);
}

function cCookie_toFilePhantomJS(){
	$cookie = new cCookie();
	$cookie->open(FILE_NAME);
	$cookie->delete('test1.ru');
	$cookie->create('PHPSESSID', 'asdf123465', '.test1.ru', '/', date('D, d-M-y H:i:s', time()+100000) . ' GMT', false, false, true);
	$cookie->create('testName', 'testValue', '.test1.ru', '/', date('D, d-M-y H:i:s', time()+100000) . ' GMT', false, false, true);
	$cookie->toFilePhantomJS($cookie->getCookies('test1.ru'));
	$testCookie = $cookie->fromFilePhantomJS();
	return $testCookie['PHPSESSID']['value'] == 'asdf123465' && $testCookie['testName']['value'] == 'testValue';
}

function cCookie_fromPhantomJS(){
	$phantomJsCookie = '[General]
cookies="@Variant(\0\0\0\x7f\0\0\0\x16QList<QNetworkCookie>\0\0\0\0\x1\0\0\0\x2\0\0\0SPHPSESSID=asdf123465; expires=Sat, 25-Jan-14 16:57:33 GMT; domain=.test1.ru; path=/\0\0\0QtestName=testValue; expires=Sat, 25-Jan-14 16:57:33 GMT; domain=.test1.ru; path=/)"
';
	$cookies = cCookie::fromPhantomJS($phantomJsCookie);
	return ($cookies['testName']['value'] == 'testValue' && $cookies['PHPSESSID']['value'] == 'asdf123465');
}

function cCookie_fromFilePhantomJS(){
	$cookie = new cCookie();
	$cookie->open(FILE_NAME);
	$cookie->delete('.test1.ru');
	$cookie->creates($cookie->fromFilePhantomJS());
	$testCookie = $cookie->getCookies('test1.ru');
	return ($testCookie['testName']['value'] == 'testValue' && $testCookie['PHPSESSID']['value'] == 'asdf123465');
}

function cCookie_delete(){
	$cookie = new cCookie();
	$cookie->open(FILE_NAME);
	$cookie->delete('test1.ru', 'PHPSESSID');
	$testCookie = $cookie->getCookies('test1.ru');
	return !$testCookie['PHPSESSID'];
}

