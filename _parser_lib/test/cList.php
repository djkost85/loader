<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 14.12.13
 * Time: 19:01
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
require_once dirname(__FILE__) . '/cnfg_test.php';
use GetContent\cList as cList;
echo "cList<br/>\n";

define('FILE_NAME', dirname(__FILE__).'/tmp/testCFile.txt');

$functions = array(
	'create',
	'write',
	'addLevel',
	'addSubLevel',
	'findByValue',
	'findByKey',
	'push',
	'getRandom',
	'deleteLevel',
	//'deleteList',
);

runTest($functions);

function create(){
	$list = new cList();
	$list->create(FILE_NAME);
	$level =& $list->getLevel('/');
	return (file_exists(FILE_NAME) && is_array($level));
}

function write(){
	$key = 'name';
	$value = 'Nafanya';
	$list = new cList();
	$list->open(FILE_NAME);
	$list->write('/',$value, $key);
	$list->update();
	$test = $list->getValue('/', $key);
	return $test === $value;
}

function addLevel(){
	$levelName = 'new_level';
	$testData = 'test';
	$list = new cList();
	$list->open(FILE_NAME);
	$list->addLevel($levelName);
	$list->write($levelName, $testData);
	$list->update();
	return ($list->getLevel($levelName) && in_array($testData, $list->getLevel($levelName)));
}

function addSubLevel(){
	$parentLevel = 'new_level';
	$subLevel = 'sub_level';
	$testData = 'test';
	$list = new cList();
	$list->open(FILE_NAME);
	$list->addLevel($subLevel, $parentLevel);
	$list->write($subLevel, $testData);
	$list->update();
	return ($list->getLevel($subLevel) && in_array($testData, $list->getLevel($subLevel)));
}

function findByValue(){
	$levelName = 'new_level';
	$testData = 'test';
	$list = new cList();
	$list->open(FILE_NAME);
	return $list->findByValue($levelName, $testData);
}

function findByKey(){
	$level = 'new_level';
	$key = 'sub_level';
	$value = 'test';
	$list = new cList();
	$list->open(FILE_NAME);
	return ($list->findByKey($level, $key) == $value || in_array( $value, $list->findByKey($level, $key)));
}

function push(){
	$levelName = 'sub_level';
	$testData = array(1,2,3,4,5,6,7,8);
	$list = new cList();
	$list->open(FILE_NAME);
	$list->push($levelName, $testData);
	$list->update();
	return $list->findByValue($levelName, $testData[2]);
}

function getRandom(){

	$levelName = 'sub_level';
	$testData = array(array(1,2,3,4,5,6,7,8),'test');
	$list = new cList();
	$list->open(FILE_NAME);
	return in_array($list->getRandom($levelName), $testData) !== false ? true : false;

}

function deleteLevel(){
	$parentLevel = 'new_level';
	$subLevel = 'sub_level';
	$list = new cList();
	$list->open(FILE_NAME);
	$list->clear($subLevel, $parentLevel);
	return !$list->getLevel($subLevel);
}

function deleteList(){
	$list = new cList();
	$list->open(FILE_NAME);
	return $list->deleteList();
}