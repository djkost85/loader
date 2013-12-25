<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 14.12.13
 * Time: 19:01
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
require_once dirname(__FILE__) . '/../include.php';
use GetContent\cList as cList;

define('FILE_NAME', dirname(__FILE__).'/tmp/testCFile.txt');

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

function push(){
	$list = new cList();
	$list->open(FILE_NAME);

}