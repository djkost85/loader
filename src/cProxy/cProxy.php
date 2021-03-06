<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 12.01.14
 * Time: 18:53
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace GetContent;

class cProxy {

	/**
	 * @var cList
	 */
	protected $_list;
	protected $_dirList;
	protected $_nameList;
	protected $_listExt = 'proxy';
	protected $_defaultNameList = 'all';
	protected $_deleteProxy = false;
	protected $_proxyFunction = array(
		'anonym',
		'referer',
		'post',
		'get',
		'cookie',
		'country',
		'last_check',
		'starttransfer',
		'upload_speed',
		'download_speed',
		'source',
		'protocol'
	);

	/**
	 * @param mixed $nameList
	 */
	public function setNameList($nameList) {
		$this->_nameList = $nameList;
	}

	/**
	 * @return mixed
	 */
	public function getNameList() {
		return $this->_nameList;
	}

	protected function getListFileName($name = false){
		return $this->getDirList() . DIRECTORY_SEPARATOR . ($name ? $name : $this->getNameList()) . '.' . $this->_listExt;
	}

	/**
	 * @param string $defaultNameList
	 */
	public function setDefaultNameList($defaultNameList) {
		$this->_defaultNameList = $defaultNameList;
	}

	/**
	 * @return string
	 */
	public function getDefaultListName() {
		return $this->_defaultNameList;
	}

	/**
	 * @param string $dirList
	 */
	public function setDirList($dirList) {
		$this->_dirList = $dirList;
	}

	/**
	 * @return string
	 */
	public function getDirList() {
		return $this->_dirList;
	}

	/**
	 * @param array $proxyFunction
	 */
	public function setProxyFunction($proxyFunction) {
		$this->_proxyFunction = $proxyFunction;
	}

	/**
	 * @return array
	 */
	public function getProxyFunction() {
		return $this->_proxyFunction;
	}

	/**
	 * @param boolean $deleteProxy
	 */
	public function setDeleteProxy($deleteProxy) {
		$this->_deleteProxy = $deleteProxy;
	}

	/**
	 * @return boolean
	 */
	public function hasDeleteProxy() {
		return $this->_deleteProxy;
	}

	public function  getList(){
		return $this->_list->getLevel($this->_list->getMainLevelName());
	}

	function __construct(){
		$this->setNameList($this->getDefaultListName());
		$this->_list = new cList();
		$this->setDirList(__DIR__ . DIRECTORY_SEPARATOR . 'proxy_list');
	}

	public function selectList($name){
		if($this->listExist($name)){
			$this->setNameList($name);
			$this->_list->open($this->getListFileName());
		} else {
			$this->createList($name);
		}
	}

	/**
	 * Создает профиль прокси адресов
	 * @param string $name      Название
	 * @param string $checkUrl  Проверочный URL
	 * @param array  $checkWord Проверочные регулярные выражения
	 * @param array  $function  Перечень поддерживаемых функций
	 * @param bool   $needUpdate
	 */
	public function createList($name, $checkUrl = "http://ya.ru", $checkWord = array("#yandex#iUm"), $function = array(), $needUpdate = false) {
		$this->setNameList($name);
		$this->_list->open($this->getListFileName());
		$this->setListOption('url', $checkUrl);
		$this->setListOption('check_word', $checkWord);
		$this->setListOption('function', $function);
		$this->setListOption('need_update', $needUpdate);
		$this->setListOption('content', array());
	}

	public function deleteList($name = false){
		if($name){
			$this->selectList($name);
		}
		$this->_list->deleteList();
	}

	public function getListOption($name){
		return $this->_list->getValue($this->_list->getMainLevelName(), $name);
	}

	public function setListOption($name, $value){
		$this->_list->write($this->_list->getMainLevelName(), $value, $name);
		$this->_list->update();
	}

	public function getAllNameList() {
		$fileList = glob($this->getDirList() . DIRECTORY_SEPARATOR . '*.' . $this->_listExt);
		$proxyListArray = array();
		foreach ($fileList as $value) {
			if (preg_match('%(?<name_list>[^/\\\\]+)\.' . $this->_listExt . '$%iUm', $value, $match)) {
				$proxyListArray[] = $match['name_list'];
			}
		}
		return $proxyListArray;
	}

	public function listExist($name){
		return in_array($name, $this->getAllNameList());
	}

	public function deleteInList($proxy){
		return $this->hasDeleteProxy() ? $this->_list->clear($proxy, 'content') : false;

	}
	//TODO: make that function pls
	/*public function removeAllRentFromKey($key){

	}*/

	/**
	 * @param string $proxy
	 * @param array  $properties self::_proxyFunction
	 */
	public function addProxy($proxy, $properties = array()){
		$this->_list->addLevel($proxy, 'content');
		$this->_list->write($proxy, $proxy, 'proxy');
		foreach($this->getProxyFunction() as $function){
			$this->_list->write($proxy, isset($properties[$function]) ? $properties[$function] : null, $function);
		}
		$this->_list->update();
	}

	public function getProxy($key = false, $url = false){
		if(!$key && !$url) {
			$proxy = $this->_list->getNextRecord('content');
			return is_array($proxy) ? $proxy : false;
		}
		return false;
	}

	public function shuffleProxyList(){
		return $this->_list->shuffleList('content');
	}

	public function loadProxy($url){
		$proxyListPage = file_get_contents($url);
		$proxy = array();
		if(preg_match_all('#(?<proxy>(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(:{1}\d{1,10})))#ims',$proxyListPage,$matches)){
			foreach($matches['proxy'] as $findProxy){
				$proxy[$findProxy] = array('proxy' => $findProxy);
			}
			$this->_list->write('content', $proxy);
			return $proxy;
		} else {
			return false;
		}
	}

	protected function loadList(&$proxyList, $proxySource){
		foreach(explode("\n", $proxySource) as $challenger){
			if(cStringWork::isIp($challenger)){
				$proxyList['content'][$challenger]['proxy'] = $challenger;
			}
		}
	}
} 