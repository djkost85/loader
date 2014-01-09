<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 09.01.14
 * Time: 9:21
 * Email: bpteam22@gmail.com
 */
require_once dirname(__FILE__) . '/cnfg.php';
use GetContent\cCookie as cCookie;

define('FILE_NAME', 'testCookies');

$functions = array(
	'create',

);

runTest($functions);

function create(){
	$cookie = new cCookie();
	$cookie->open(FILE_NAME);
	$cookie->create('testName', 'testValue', '.test1.ru', '/lib3', date('l, d-M-y H:i:s e', time() + 43200), false, false, true);
	$cookie->create('2testName', '2testValue', '.test1.ru', '/', date('l, d-M-y H:i:s e', time() + 63200), true, true, true);
	$cookies = $cookie->getCookies('http://www.test1.ru/blablalba/22');
	var_dump($cookies);
}