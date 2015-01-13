<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 05.12.13
 * Time: 0:06
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
require_once __DIR__ . '/cnfg_test.php';
use GetContent\cFile as cFile;
echo "cFile<br/>\n";

define('FILE_NAME', __DIR__.'/support/testCFile.txt');

$functions = array(
	'testCreateFile',
	'testOpenFile',
	'testWriteFile',
	'testReadFile',
	'testClearFile',
	'testWriteLock',
	'testReadLock',
	'testClose',
	'testCantWriteLock',
	'testCantReadLock',
	'testCantDeleteLock',
	'testCantBlockLock',
	'testDeleteLock',
);

runTest($functions, 'cFile_');

function cFile_testCreateFile(){
	$file = new cFile(FILE_NAME);
	$file->open(FILE_NAME);
	return file_exists(FILE_NAME);
}

function cFile_testOpenFile(){
	$file = new cFile(FILE_NAME);
	return $file->open(FILE_NAME);
}

function cFile_testWriteFile(){
	$file = new cFile(FILE_NAME);
	return $file->write('hello world'. microtime(true) . "\n");
}

function cFile_testReadFile(){
	$file = new cFile(FILE_NAME);
	return ($file->read() !== false);
}

function cFile_testClearFile(){
	$file = new cFile(FILE_NAME);
	return ($file->clear() !== false);
}

function cFile_testWriteLock(){
	$fileBlock = new cFile(FILE_NAME);
	$fileBlock->lock();
	$res = $fileBlock->write("hello ". microtime(true) . "\n");
	$fileBlock->free();
	return $res;
}

function cFile_testReadLock(){
	$fileBlock = new cFile(FILE_NAME);
	$fileBlock->lock();
	$res = $fileBlock->read();
	$fileBlock->free();
	return $res;
}

function cFile_testDeleteLock(){
	$fileBlock = new cFile(FILE_NAME);
	$fileBlock->lock();
	return $fileBlock->delete();
}

function cFile_testClose(){
	$file = new cFile(FILE_NAME);
	return $file->close();
}

function cFile_testCantWriteLock(){
	$fileBlock = new cFile(FILE_NAME);
	$file = new cFile(FILE_NAME);
	$fileBlock->lock();
	$res = $file->write('not hello'. microtime(true) . "\n");
	$fileBlock->free();
	return (!$res);
}

function cFile_testCantReadLock(){
	$fileBlock = new cFile(FILE_NAME);
	$file = new cFile(FILE_NAME);
	$fileBlock->lock();
	$res = $file->read();
	$fileBlock->free();
	return (!$res);
}

function cFile_testCantDeleteLock(){
	$fileBlock = new cFile(FILE_NAME);
	$file = new cFile(FILE_NAME);
	$fileBlock->lock();
	$res = $file->delete();
	$fileBlock->free();
	return (!$res);
}

function cFile_testCantBlockLock(){
	$fileBlock = new cFile(FILE_NAME);
	$file = new cFile(FILE_NAME);
	$fileBlock->lock();
	$res = $file->lock();
	$fileBlock->free();
	return (!$res);
}