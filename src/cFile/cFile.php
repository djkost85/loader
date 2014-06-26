<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 04.12.13
 * Time: 0:11
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace GetContent;


/**
 * Class cFile
 * Класс для работы с файлами, распределение доступа к файлам, CRUD
 * @package GetContent
 * @author  Evgeny Pynykh <bpteam22@gmail.com>
 */
class cFile {

	private $_currentPath;
	/**
	 * @var resource
	 */
	private $_head = null;
	private $_name;
	private $_own;
	/**
	 * Ожидать пока освободиться файл или нет
	 * @var bool
	 */
	private $_waitFree = false;

	/**
	 * @var bool Блокировать файл или нет
	 */
	private $_lockAccess = false;

	/**
	 * @param boolean $lockAccess
	 */
	public function setLockAccess($lockAccess) {
		$this->_lockAccess = $lockAccess;
	}

	/**
	 * @return boolean
	 */
	public function getLockAccess() {
		return $this->_lockAccess;
	}



	/**
	 * @param string $currentPath
	 */
	public function setCurrentPath($currentPath) {
		$this->_currentPath = $currentPath;
	}

	/**
	 * @return string
	 */
	public function getCurrentPath() {
		return $this->_currentPath;
	}

	/**
	 * @param string $val
	 */
	public function setHead($val) {
		$this->_head = $val;
	}

	/**
	 * @return resource
	 */
	public function &getHead() {
		return $this->_head;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->setCurrentPath(dirname($name));
		$this->_name = $name;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * @param mixed $own
	 */
	public function setOwn($own) {
		$this->_own = $own;
	}

	/**
	 * @return mixed
	 */
	public function getOwn() {
		return $this->_own;
	}

	private function access($function){
		if($this->getOwn() || !$this->getLockAccess()){
			$res = $function(func_get_arg(1), func_get_arg(2));
		} elseif($this->lock()){
			$res = $function(func_get_arg(1), func_get_arg(2));
			$this->free();
		} else {
			$res = false;
		}
		return $res;
	}

	/**
	 * @param boolean $waitFree
	 */
	public function setWaitFree($waitFree) {
		$this->_waitFree = $waitFree;
	}

	/**
	 * @return boolean
	 */
	public function getWaitFree() {
		return $this->_waitFree;
	}

	function __construct($name = null){
		if($name){
			$this->open($name);
		}
	}

	function __destruct(){
		$this->close();
	}

	public function open($name){
		if($this->getHead()){
			$this->close();
		}
		$this->setName($name);
		$this->setHead(fopen($this->getName(),'a+'));
		return $this->getHead();
	}

	public function close(){
		$this->free();
		return @fclose($this->getHead());
	}

	/**
	 * Блокировка файла от других процессов
	 * @return bool
	 */
	public function lock(){
		if (is_resource($this->getHead())) {
			if($this->getWaitFree()){
				$this->setOwn(flock($this->getHead(), LOCK_EX));
			} else {
				$this->setOwn(flock($this->getHead(), LOCK_EX | LOCK_NB));
			}
			return $this->getOwn();
		} else {
			return false;
		}
	}

	public function free(){
		if (is_resource($this->getHead())) {
			fflush($this->getHead());
			$this->setOwn(!flock($this->getHead(), LOCK_UN));
			return $this->getOwn();
		} else {
			return false;
		}
	}

	public function write($data){
		$res = $this->access('fwrite', $this->getHead(), $data);
		fflush ($this->getHead());
		return $res;
	}

	/**
	 * Читает из файла, чтение производится с учетом блокировки, если файл блокирован вернет false, или файл не доступен
	 * @return bool|string
	 */
	public function read(){
		rewind($this->getHead());
		clearstatcache(true, $this->getName());
		$fSize = filesize($this->getName());
		$data = '';
		if($fSize){
			$data = $this->access('fread', $this->getHead(), $fSize);
		}
		if (strlen($data) == $fSize && $data !== false) {
			return $data;
		}
		return false;
	}

	public function delete(){
		if($this->lock()){
			$this->close();
			return unlink($this->getName());
		}
		return false;
	}

	public function clear(){
		return $this->access('ftruncate', $this->getHead(), 0);
	}

} 