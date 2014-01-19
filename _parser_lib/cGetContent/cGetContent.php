<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 12.01.14
 * Time: 15:47
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace GetContent;


class cGetContent {

	protected $_userAgentList = array();

	/**
	 * @param string $type
	 */
	public function setUserAgentList($type = 'desktop') {
		$this->_userAgentList = require dirname(__FILE__) . DIRECTORY_SEPARATOR . "user_agent_$type.php";
	}

	/**
	 * @return array|mixed
	 */
	public function getUserAgentList() {
		return $this->_userAgentList;
	}

	public function getRandomUserAgent(){
		return $this->_userAgentList[array_rand($this->_userAgentList,1)];
	}

	function __construct(){
		$this->setUserAgentList('desktop');
	}

}