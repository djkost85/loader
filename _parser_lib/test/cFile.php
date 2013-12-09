<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 05.12.13
 * Time: 0:06
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
require_once dirname(__FILE__) . '/../include.php';
use GetContent\cFile as cFile;

define('FILE_NAME', dirname(__FILE__).'/tmp/testCFile.txt');

$functions = array(
	'testCreateFile',
	'testOpenFile',
	'testWriteFile',
	'testReadFile',
	//'testClearFile',
	'testWriteLock',
	'testReadLock',
	'testClose',
	'testCantWriteLock',
	'testCantReadLock',
	//'testCantDeleteLock',
	'testCantBlockLock',
	//'testDeleteLock',
);

$start = microtime(true);
echo date("[H:i:s Y/m/d]", $start)."\n<br>\n";
$echo = '';
foreach($functions as $function){
	$echo .= $function;
	if($function()){
		$echo .= " success \n<br>\n";
	} else {
		$echo .= " <b>ERROR</b> \n<br>\n";
	}
}
echo $echo;
$end = microtime(true);
echo date('[H:i:s Y/m/d]', $end)."\n<br>\n";
echo '[~'.($end-$start).']';
function testCreateFile(){
	$file = new cFile(FILE_NAME);
	$file->open(FILE_NAME);
	return file_exists(FILE_NAME);
}

function testOpenFile(){
	$file = new cFile(FILE_NAME);
	return $file->open(FILE_NAME);
}

function testWriteFile(){
	$file = new cFile(FILE_NAME);
	return $file->write('hello world');
}

function testReadFile(){
	$file = new cFile(FILE_NAME);
	return ($file->read() !== false);
}

function testClearFile(){
	$file = new cFile(FILE_NAME);
	return ($file->clear() !== false);
}

function testWriteLock(){
	$fileBlock = new cFile(FILE_NAME);
	$fileBlock->lock();
	$res = $fileBlock->write('hello');
	$fileBlock->free();
	return $res;
}

function testReadLock(){
	$fileBlock = new cFile(FILE_NAME);
	$fileBlock->lock();
	$res = $fileBlock->read();
	$fileBlock->free();
	return $res;
}

function testDeleteLock(){
	$fileBlock = new cFile(FILE_NAME);
	$fileBlock->lock();
	return $fileBlock->delete();
}

function testClose(){
	$file = new cFile(FILE_NAME);
	return $file->close();
}

function testCantWriteLock(){
	$fileBlock = new cFile(FILE_NAME);
	$file = new cFile(FILE_NAME);
	$fileBlock->lock();
	$res = $file->write('not hello');
	$fileBlock->free();
	return (!$res);
}

function testCantReadLock(){
	$fileBlock = new cFile(FILE_NAME);
	$file = new cFile(FILE_NAME);
	$fileBlock->lock();
	$res = $file->read();
	$fileBlock->free();
	return (!$res);
}

function testCantDeleteLock(){
	$fileBlock = new cFile(FILE_NAME);
	$file = new cFile(FILE_NAME);
	$fileBlock->lock();
	$res = $file->delete();
	$fileBlock->free();
	return (!$res);
}

function testCantBlockLock(){
	$fileBlock = new cFile(FILE_NAME);
	$file = new cFile(FILE_NAME);
	$fileBlock->lock();
	$res = $file->lock();
	$fileBlock->free();
	return (!$res);
}