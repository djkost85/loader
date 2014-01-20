<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 16.01.14
 * Time: 13:09
 * Email: bpteam22@gmail.com
 */

require_once dirname(__FILE__) . '/cnfg_test.php';
use GetContent\cPhantomJS as cPhantomJS;
//echo "cPhantomJS<br/>\n";

$functions = array(
	'test',
);

//runTest($functions);


	$phantom = new cPhantomJS(PHANTOMJS_EXE);
	$phantom->test();
