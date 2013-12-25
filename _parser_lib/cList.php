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
	 * @var int максимальная глубина списка
	 */
	private $_maxLevel = 1000;

	/**
	 * @param int $maxLevel
	 */
	public function setMaxLevel($maxLevel) {
		$this->_maxLevel = $maxLevel;
	}

	/**
	 * @return int
	 */
	public function getMaxLevel() {
		return $this->_maxLevel;
	}
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
	public function &getList() {
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


	public function create($name){
		$this->_file->open($name);
		$this->clear();
		$this->save();
	}
	public function open($name){
		$this->_file->open($name);
		return $this->read();
	}
	public function read(){
		$json = json_decode($this->_file->read(),true,$this->getMaxLevel());
		if(!$json){
			return false;
		}
		$this->setList($json);
		return $this->getList();
	}

	public function save(){
		$this->_file->lock();
		$this->_file->clear();
		$this->_file->write(json_encode($this->getList()));
		$this->_file->close();
	}

	public function update(){
		$this->_file->lock();
		$newList = $this->getList();
		$oldList = $this->read();
		if(is_array($newList) && is_array($oldList)){
			$newList = array_merge($newList, $oldList);
			$this->setList($newList);
			$this->save();
			return true;
		} else {
			$this->_file->free();
			return false;
		}
	}


	public function &find($level, $key = 'key', $value = 'value'){
		$level =& $this->getLevel($level);
		if(is_array($level) && array_key_exists($key, $level) && $level[$key] == $value){
			return $level;
		} else {
			return false;
		}
	}
	/**
	 * @param string     $level имя уровня
	 * @param array|null $levelData поиск в списке
	 * @return bool
	 */
	public function &getLevel($level, &$levelData = null){
		if($levelData === null){
			$levelData =& $this->_list;
		}
		if(is_array($levelData)){
			if(array_key_exists($level, $levelData)){
				return $levelData[$level];
			} else {
				foreach($levelData as &$subLevel){
					$result =& $this->getLevel($level,$subLevel);
					if($result){
						return $result;
					}
				}
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * @param string      $key
	 * @param bool|string $parent
	 * @return bool
	 */
	public function addLevel($key,$parent = false){
		if($parent){
			$level =& $this->getLevel($parent);
		} else {
			$level =& $this->_list;
		}
		if(!array_key_exists($key, $level)){
			$level[$key] = array();
		}
		return array_key_exists($key, $level);
	}

	public function &getRandom($level){
		$level =& $this->getLevel($level);
		if(is_array($level)){
			return $level[array_rand($level,1)];
		} else {
			return false;
		}
	}

	public function push($level, $data){
		$level =& $this->getLevel($level);
		if($level){
			$level[] = $data;
			return true;
		} else {
			return false;
		}
	}

	public function write($level, $value, $key = null){
		$level =& $this->getLevel($level);
		if(isset($key)){
			$level[$key] = $value;
		} else {
			$this->push($level, $value);
		}
	}

	public function clear(){
		$this->setList(array( "/" => array() ));
	}

	public function &getValue($level, $key){
		$level =& $this->getLevel($level);
		if(is_array($level) && array_key_exists($key, $level)){
			return $level[$key];
		} else {
			return false;
		}
	}

} 