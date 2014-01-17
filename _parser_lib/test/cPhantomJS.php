<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 16.01.14
 * Time: 13:09
 * Email: bpteam22@gmail.com
 */

require_once dirname(__FILE__) . '/cnfg.php';
use GetContent\cPhantomJS as cPhantomJS;
//echo "cPhantomJS<br/>\n";

$functions = array(
	'test',
);

//runTest($functions);


	$phantom = new cPhantomJS('C:\phantomjs\phantomjs.exe');
	$phantom->test();
