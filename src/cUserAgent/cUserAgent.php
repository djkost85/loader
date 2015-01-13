<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 26.01.14
 * Time: 13:33
 * Project: get_content
 * @author: Evgeny Pynykh <bpteam22@gmail.com>
 * @link bpteam.net
 */

namespace GetContent;


class cUserAgent {

	private $_userAgentList = array();

	function __construct($type = 'desktop'){
		$this->setUserAgentList($type);
	}
	/**
	 * @param string $type 'desktop' || 'mobile' || 'bot'
	 */
	public function setUserAgentList($type = 'desktop') {
		$this->_userAgentList = require __DIR__ . DIRECTORY_SEPARATOR . 'useragent_list' . DIRECTORY_SEPARATOR . $type . ".php";
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
} 