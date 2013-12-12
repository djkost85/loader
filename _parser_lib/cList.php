<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 12.12.13
 * Time: 17:23
 * Email: bpteam22@gmail.com
 */

namespace GetContent;

/**
 * Class cList
 * @package GetContent
 * Класс для работы с текстовыми данными и хранит их в JSON
 */
class cList {

	/**
	 * @var array
	 */
	private $_list;

	/**
	 * @param array $list
	 */
	public function setList($list) {
		$this->_list = $list;
	}

	/**
	 * @return array
	 */
	public function getList() {
		return $this->_list;
	}
	/**
	 * @var cFile $_file
	 * Класс для работы с файлами
	 */
	private $_file;

	/**
	 * @param \GetContent\cFile $file
	 */
	public function setFile($file) {
		$this->_file = $file;
	}

	/**
	 * @return \GetContent\cFile
	 */
	public function getFile() {
		return $this->_file;
	}

	function __construct($name = false){
		$this->setFile(new cFile());
		if($name){
			$this->open($name);
		}
	}

	public function open($name){
		$this->_file->open($name);
		$json = json_decode($this->_file->read(),true);
		if(!$json){
			return false;
		}
		$this->setList($json);
		return true;
	}

	public function find($level, $needle){

	}

	public function getLevel($level, $needle){

	}

	public function addLevel($name,$parent = false){

	}

	public function getRandom($level){

	}

} 