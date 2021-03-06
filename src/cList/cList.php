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

	private $_mainLevelName = "/";
	private $_maxLevel = 1000;
	private $_list;
	/**
	 * @var cFile $_file
	 * Класс для работы с файлами
	 */
	private $_file;

	/**
	 * @param string $mainLevelName
	 */
	public function setMainLevelName($mainLevelName) {
		$this->_mainLevelName = $mainLevelName;
	}

	/**
	 * @return string
	 */
	public function getMainLevelName() {
		return $this->_mainLevelName;
	}

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
		if(!$this->read()){
			$this->create($name);
		}
		return $this->read();
	}
	public function &read(){
		$json = json_decode($this->_file->read(),true,$this->getMaxLevel());
		$this->setList($json ? $json : array());
		return $this->getList();
	}

	public function save(){
		$this->_file->lock();
		$this->_file->clear();
		$this->_file->write(json_encode($this->getList()));
		$this->_file->free();
	}

	public function update(){
		$this->_file->lock();
		$newList = $this->getList();
		$oldList = $this->read();
		if(is_array($newList) && is_array($oldList)){
			$newList = array_merge($oldList, $newList);
			$this->setList($newList);
			$this->save();
			return true;
		} else {
			$this->_file->free();
			return false;
		}
	}

	public function deleteList(){
		return $this->_file->delete();
	}

	public function &findByValue($level, $value){
		$level =& $this->getLevel($level);
		$keys = array();
		if(is_array($level)) {
			foreach($level as $key => $data){
				if($data == $value || (is_array($data) && in_array($value, $data))){
					$keys[] = $key;
				}
			}
		}
		return $keys;
	}

	public function &findByKey($level, $key){
		$level =& $this->getLevel($level);
		if(is_array($level)){
			if(array_key_exists($key, $level)){
				return $level[$key];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	/**
	 * @param string     $level имя уровня
	 * @param array|null $levelData данные в которых ищем необходимый уровень
	 * @return array|null
	 */
	public function &getLevel($level, &$levelData = null){
		if($levelData === null){
			$levelData =& $this->getList();
		}
		if(is_array($levelData)){
			if(array_key_exists($level, $levelData)){
				return $levelData[$level];
			} else {
				foreach($levelData as &$subLevel){
					@$result =& $this->getLevel($level,$subLevel);
					if($result !== null){
						return $result;
					}
				}
			}
		}
		$null = null;
		return $null;
	}

	/**
	 * @param string      $levelName
	 * @param bool|string $parentName
	 * @return bool
	 */
	public function addLevel($levelName,$parentName = null){
		$parentName = $parentName ? $parentName : $this->getMainLevelName();
		$level =& $this->getLevel($parentName);
		if(!array_key_exists($levelName, $level)){
			$level[$levelName] = array();
		}
		return array_key_exists($levelName, $level);
	}

	public function &getRandomRecord($levelName){
		$level =& $this->getLevel($levelName);
		if(is_array($level)){
			return $level[array_rand($level,1)];
		} else {
			return false;
		}
	}

	public function shuffleList($levelName){
		$level =& $this->getLevel($levelName);
		if(is_array($level)){
			return shuffle($level);
		} else {
			return false;
		}
	}

	public function getNextRecord($levelName){
		$level =& $this->getLevel($levelName);
		$data = $level?next($level):null;
		if($data === false){
			$data = reset($level);
		}
		return $data;
	}

	public function push($levelName, $data){
		$level =& $this->getLevel($levelName);
		if(is_array($level)){
			$level[] = $data;
			return true;
		} else {
			return false;
		}
	}

	public function write($levelName, $value, $key = null){
		$levelData =& $this->getLevel($levelName);
		if(!empty($key)){
			$levelData[$key] = $value;
		} else {
			$this->push($levelName, $value);
		}
	}

	public function clear($levelName = false, $parentName = false){
		if(!$levelName){
			$list =& $this->getList();
			$list[$this->getMainLevelName()] = array();
		} elseif($parentName) {
			$parent =& $this->getLevel($parentName);
			$parent[$levelName] = array();
		} else {
			$level =& $this->getLevel($levelName);
			if($level){
				$level = array();
			}
		}
		return $this->update();
	}

	public function &getValue($level, $key){
		$level =& $this->getLevel($level);
		if(is_array($level) && array_key_exists($key, $level)){
			return $level[$key];
		} else {
			return null;
		}
	}

} 