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

	/**
	 * @var string $_currentPath Текущая папка
	 */
	private $_currentPath;

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
	 * @var resource $_fileHead Заголовок указываеющий на файл
	 */
	private $_head;

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
	 * @var string $_fileName имя текущего файла
	 */
	private $_name;

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

	private $_own;

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

	function __construct($name = null){
		if($name){
			$this->open($name);
		}
	}

	function __destruct(){
		$this->close();
	}

	public function open($name){
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
	 * @param bool $waitFree ждать пока освободиться или нет
	 * @return bool
	 */
	public function lock($waitFree = false){
		if (is_resource($this->getHead())) {
			if($waitFree){
				return flock($this->getHead(), LOCK_EX);
			} else {
				return flock($this->getHead(), LOCK_EX | LOCK_NB);
			}
		} else {
			return false;
		}
	}

	public function free(){
		if (is_resource($this->getHead())) {
			fflush($this->getHead());
			return flock($this->getHead(), LOCK_UN);
		} else {
			return false;
		}
	}

	public function write($data){
		$res = fwrite($this->getHead(),$data);
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
			$data = fread($this->getHead(), $fSize);
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
		return ftruncate($this->getHead(), 0);
	}

} 