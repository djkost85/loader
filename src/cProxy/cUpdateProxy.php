<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 26.01.14
 * Time: 20:55
 * Project: get_content
 * @author: Evgeny Pynykh <bpteam22@gmail.com>
 * @link bpteam.net
 */

namespace GetContent;


class cUpdateProxy extends cProxy {

	private $_serverIp;
	private $_checkFunctionUrl;
	private $_dirSource;
	private $_urlCheckServerIp;
	private $_archiveProxyFile = 'archive';
	/**
	 * @var cMultiCurl
	 */
	private $_curl;

	/**
	 * @param string $checkFunctionUrl
	 */
	public function setCheckFunctionUrl($checkFunctionUrl) {
		$this->_checkFunctionUrl = $checkFunctionUrl;
	}

	/**
	 * @return string
	 */
	public function getCheckFunctionUrl() {
		return $this->_checkFunctionUrl;
	}

	/**
	 * @param string $dirSource
	 */
	public function setDirSource($dirSource) {
		$this->_dirSource = $dirSource;
	}

	/**
	 * @return string
	 */
	public function getDirSource() {
		return $this->_dirSource;
	}

	public function getAllSourceName(){
		$name = array();
		foreach (glob($this->getDirSource() . DIRECTORY_SEPARATOR . "*.php") as $fileModule) {
			$name[] = basename($fileModule, '.php');
		}
		return $name;
	}

	/**
	 * @param string $urlCheckServerIp
	 */
	public function setUrlCheckServerIp($urlCheckServerIp) {
		$this->_urlCheckServerIp = $urlCheckServerIp;
	}

	/**
	 * @return string
	 */
	public function getUrlCheckServerIp() {
		return $this->_urlCheckServerIp;
	}


	function __construct($checkUrl = 'http://test1.ru/proxy_check.php', $port = 80, $serverIp = '', $urlCheckServerIp = 'http://bpteam.net/server_ip.php'){
		parent::__construct();
		$this->_curl = new cMultiCurl();
		$this->setUrlCheckServerIp($urlCheckServerIp);
		$this->_curl->setTypeContent('text');
		$this->_curl->setEncodingAnswer(false);
		$this->_curl->setDefaultOption(CURLOPT_PORT, $port);
		$this->setDirSource(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'site_source');
		$this->setCheckFunctionUrl($checkUrl);
		$this->setServerIp($serverIp);
	}


	/**
	 * @param string       $proxy
	 * @param string       $answer
	 * @param array        $source
	 * @param array        $protocol
	 * @param null|array   $curlInfo
	 * @return array|bool
	 */
	private function genInfo($proxy, $answer, $source = array(), $protocol = array('http'=> true), $curlInfo = null) {
		if (preg_match('%^[01]{5}%', $answer) && preg_match_all('%(?<fun_status>[01])%', $answer, $matches)) {
			$infoProxy['proxy'] = $proxy;
			$infoProxy['source'] = $source;
			$infoProxy['protocol'] = $protocol;
			$infoProxy['anonym'] = (bool)$matches['fun_status'][0];
			$infoProxy['referer'] = (bool)$matches['fun_status'][1];
			$infoProxy['post'] = (bool)$matches['fun_status'][2];
			$infoProxy['get'] = (bool)$matches['fun_status'][3];
			$infoProxy['cookie'] = (bool)$matches['fun_status'][4];
			$infoProxy['last_check'] = time();
			preg_match('%(?<ip>\d+\.\d+\.\d+\.\d+)\:\d+%ims', $infoProxy['proxy'], $match);
			$countryName = isset($match['ip']) && function_exists('geoip_country_name_by_name') ? @geoip_country_name_by_name($match['ip']) : false;
			$infoProxy['country'] = $countryName ? $countryName : 'no country';
			$infoProxy['starttransfer'] = isset($curlInfo['starttransfer_time']) ? $curlInfo['starttransfer_time'] : false;
			$infoProxy['upload_speed'] = isset($curlInfo['speed_upload']) ? $curlInfo['speed_upload'] : false;
			$infoProxy['download_speed'] = isset($curlInfo['speed_download']) ? $curlInfo['speed_download'] : false;
			return $infoProxy;
		} else {
			return array();
		}
	}

	public function updateAllList() {
		$this->updateDefaultList();
		foreach ($this->getAllNameList() as $value) {
			$this->updateList($value);
		}
	}

	public function updateDefaultList() {
		$this->selectList($this->getDefaultListName());
		$proxyList = $this->downloadAllSource();
		$proxyList['content'] = $this->checkProxyArray($proxyList['content']);
		$this->_list->write('/', $proxyList['content'], 'content');
		$this->_list->update();
	}

	public function updateList($nameList) {
		if ($nameList == $this->getDefaultListName()) {
			return null;
		}
		$this->selectList($this->getDefaultListName());
		$allProxy = $this->getList();
		$this->selectList($nameList);
		$proxyList = $this->getList();
		$proxyList['content'] = $this->getProxyByFunction($allProxy['content'], $proxyList['function']);
		$proxyList['content'] = $this->checkProxyArrayToSite($proxyList['content'], $proxyList['url'], $proxyList['check_word']);
		$this->_list->write('/', $proxyList['content'], 'content');
		$this->_list->update();
	}

	public function downloadAllSource() {
		$proxy['content'] = array();
		foreach (glob($this->getDirSource() . DIRECTORY_SEPARATOR . "*.php") as $fileModule) {
			$tmpProxy = require $fileModule;
			if (isset($tmpProxy['content'])) {
				$proxy['content'] = array_merge($proxy['content'], $tmpProxy['content']);
			}
		}
		return $proxy;
	}

	public function downloadSource($name){
		if (in_array($name, $this->getAllSourceName())) {
			$proxy = require $this->getDirSource() . DIRECTORY_SEPARATOR . $name . '.php';
			return $proxy;
		} else {
			return array();
		}
	}

	public function getProxyByFunction($proxyList, $function = array()) {
		if (!is_array($proxyList)){
			return false;
		}
		$goodProxy = array();
		foreach ($proxyList as $challenger) {
			if($this->checkProxyFunctions($challenger, $function)){
				$goodProxy[] = $challenger;
			}
		}
		if (count($goodProxy)) return $goodProxy;
		return false;
	}

	protected function checkProxyFunctions($proxyFunctions, $needFunctions){
		foreach($needFunctions as $name => $value){
			switch(true){
				case in_array( $name, array('anonym','referer','post','get','cookie')):
					if($proxyFunctions[$name] != $value){
						return false;
					}
					break;
				case in_array($name, array('starttransfer')):
					if($proxyFunctions[$name] >= $value){
						return false;
					}
					break;
				case in_array( $name, array('country')):
					if($value){
						if((is_array($value) && !in_array( $proxyFunctions[$name], $value))
						|| (is_string($value) && $proxyFunctions[$name] != $value)){
							return false;
						}
					}
					break;
				case in_array( $name, array('last_check', 'upload_speed', 'download_speed')):
					if($proxyFunctions[$name] <= $value){
						return false;
					}
					break;
				case in_array( $name, array('source', 'protocol')):
					if(!array_key_exists( $value, $proxyFunctions[$name])){
						return false;
					}
					break;
			}
		}
		return true;
	}

	private function checkProxyArray($arrayProxy) {
		if (is_array($arrayProxy)) {
			$goodProxy = array();
			$url = $this->getCheckFunctionUrl() . '?ip=' . $this->getServerIp() . '&proxy=yandex';
			$this->_curl->setCountStream(1);
			$this->_curl->setMinSizeAnswer(5);
			$this->_curl->setDefaultOption(CURLOPT_TIMEOUT, 30);
			$this->_curl->setMaxRepeat(0);
			$this->_curl->setDefaultOption(CURLOPT_REFERER, "proxy-check.net");
			$this->_curl->setDefaultOption(CURLOPT_POST, true);
			$this->_curl->setDefaultOption(CURLOPT_POSTFIELDS, "proxy=yandex");
			$this->_curl->setTypeContent('text');
			$this->_curl->setCheckAnswer(false);
			foreach (array_chunk($arrayProxy, 150) as $challenger) {
				$this->_curl->setCountCurl(count($challenger));
				$urlList = array();
				$descriptorArray =& $this->_curl->getDescriptorArray();
				foreach ($descriptorArray as $key => &$descriptor) {
					$this->_curl->setOption($descriptor, CURLOPT_PROXY, $challenger[$key]['proxy']);
					$urlList[] = $url;
				}
				foreach ($this->_curl->load($urlList) as $key => $answer) {
					$infoProxy = $this->genInfo($challenger[$key]['proxy'], $answer, $challenger[$key]['source'], $challenger[$key]['protocol'], $descriptorArray[$key]['info']);
					if ($infoProxy) {
						$goodProxy[] = $infoProxy;
					}
				}
				$this->_curl->genNewKeyStream();
			}
			if (count($goodProxy)) {
				return $goodProxy;
			}
		}
		return array();
	}

	private function checkProxyArrayToSite($arrayProxy, $url, $checkWord) {
		if (!is_array($arrayProxy)) return array();
		$goodProxy = array();
		$this->_curl->setCountStream(1);
		$this->_curl->setTypeContent('text');
		$this->_curl->setDefaultOption(CURLOPT_POST, false);
		$this->_curl->setDefaultOption(CURLOPT_TIMEOUT, 30);
		$this->_curl->setCheckAnswer(false);
		foreach (array_chunk($arrayProxy, 100) as $challenger) {
			$this->_curl->setCountCurl(count($challenger));
			$descriptorArray =& $this->_curl->getDescriptorArray();
			$urlList = array();
			foreach ($descriptorArray as $key => &$descriptor) {
				$this->_curl->setOption($descriptor, CURLOPT_PROXY, $challenger[$key]['proxy']);
				$urlList[] = $url;
			}
			foreach ($this->_curl->load($urlList) as $key => $answer) {
				$testCount = 0;
				$countGood = 0;
				foreach ($checkWord as $valueCheckWord) {
					$testCount++;
					if (preg_match($valueCheckWord, $answer)){
						$countGood++;
					}
				}
				if ($countGood == $testCount) {
					$goodProxy[] = $challenger[$key];
				}
			}
		}
		return count($goodProxy) ? $goodProxy : array();
	}

	/**
	 * @return string
	 */
	public function getServerIp() {
		return $this->_serverIp;
	}

	/**
	 * Переделать или делать запрос на другой сервис
	 * @param $ip
	 * @return void
	 */
	public function setServerIp($ip) {
		if (!$ip) {
			$answer = file_get_contents($this->getUrlCheckServerIp());
			$ip = cStringWork::getIp($answer);
			if (!$ip[0]) exit('NO SERVER IP');
			$this->setServerIp($ip[0]);
		} else {
			$this->_serverIp = $ip;
		}
	}

	public function setUpdateList($value, $name = false){
		if($name){
			$this->selectList($name);
		}
		$this->_list->write($this->_list->getMainLevelName(), $value, 'need_update');
		$this->_list->update();
	}
} 