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


	function __construct($checkUrl){
		parent::__construct();
		$this->_curl = new cMultiCurl();
		$this->_curl->setTypeContent('text');
		$this->_curl->setEncodingAnswer(false);
		$this->setDirSource(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'site_source');
		$this->setCheckFunctionUrl($checkUrl);
	}


	/**
	 * @param array|string $proxy
	 * @param string       $answer
	 * @param string       $source
	 * @param array        $protocol
	 * @param null|array   $curlInfo
	 * @return array|bool
	 */
	private function genProxyInfo($proxy, $answer, $source = '', $protocol = array(), $curlInfo = null) {
		if (preg_match('#^[01]{5}#', $answer) && preg_match_all('#(?<fun_status>[01])#U', $answer, $matches)) {
			$infoProxy['proxy'] = $proxy;
			$infoProxy['source'][] = $source;
			$infoProxy['protocol'] = $protocol;
			$infoProxy['anonym'] = (bool)$matches['fun_status'][0];
			$infoProxy['referer'] = (bool)$matches['fun_status'][1];
			$infoProxy['post'] = (bool)$matches['fun_status'][2];
			$infoProxy['get'] = (bool)$matches['fun_status'][3];
			$infoProxy['cookie'] = (bool)$matches['fun_status'][4];
			$infoProxy['last_check'] = time();
			preg_match('%(?<ip>\d+\.\d+\.\d+\.\d+)\:\d+%ims', $infoProxy['proxy'], $match);
			$countryName = isset($match['ip']) ? @geoip_country_name_by_name($match['ip']) : false;
			$infoProxy['country'] = $countryName ? $countryName : 'no country';
			$infoProxy['starttransfer'] = isset($curlInfo['starttransfer_time']) ? $curlInfo['starttransfer_time'] : false;
			$infoProxy['upload_speed'] = isset($curlInfo['speed_upload']) ? $curlInfo['speed_upload'] : false;
			$infoProxy['download_speed'] = isset($curlInfo['speed_download']) ? $curlInfo['speed_download'] : false;
			return $infoProxy;
		} else {
			return false;
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
		$this->_list->write('content', $proxyList['content']);
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
		$this->_list->write('content', $proxyList['content']);
		$this->_list->update();
	}

	public function downloadAllSource() {
		$proxy['content'] = array();
		foreach (glob($this->getDirSource() . DIRECTORY_SEPARATOR . "*.php") as $fileModule) {
			$tmpProxy = require $fileModule;
			if (is_array($tmpProxy)) {
				$proxy['content'] = array_merge($proxy['content'], $tmpProxy['content']);
			}
		}
		return $proxy;
	}

	public function downloadSource($name){
		if (preg_match('#' . preg_quote($moduleName, '#') . '#ims', $valueProxyList)) {
			$tmp_proxy = require $valueProxyList;
			return $tmp_proxy;
		}
	}

	public function getProxyByFunction($proxyList, $function = array()) {
		if (!is_array($proxyList)){
			return false;
		}
		$goodProxy = array();
		foreach ($proxyList as $challenger) {
			$approach = false;
			if (count($function)) {
				foreach ($function as $nameFunction => $valueFunction) {
					if (in_array($nameFunction, $this->_proxyFunction) && $challenger[$nameFunction] >= $valueFunction) {
						if ($nameFunction == 'country') {
							if ($valueFunction === $challenger[$nameFunction]) {
								$approach = true;
							} else {
								$approach = false;
								break;
							}
						} else {
							$approach = true;
						}
					} else {
						$approach = false;
						break;
					}
				}
			} else {
				$approach = true;
			}
			if ($approach) {
				$goodProxy[] = $challenger;
			}
		}
		if (count($goodProxy)) return $goodProxy;
		return false;
	}

	private function checkProxyArray($arrayProxy) {
		if (is_array($arrayProxy)) {
			$goodProxy = array();
			$url = $this->getCheckFunctionUrl() . '?ip=' . $this->getServerIp() . '&proxy=yandex';
			$this->_curl->setUseProxy(true);
			$this->_curl->setCountStream(1);
			$this->_curl->setMinSizeAnswer(5);
			$this->_curl->setMaxRepeat(0);
			$this->_curl->setDefaultOption(CURLOPT_REFERER, "proxy-check.net");
			$this->_curl->setDefaultOption(CURLOPT_POST, true);
			$this->_curl->setDefaultOption(CURLOPT_POSTFIELDS, "proxy=yandex");
			$this->_curl->setTypeContent('text');
			$this->_curl->setCheckAnswer(false);
			foreach (array_chunk($arrayProxy, 100) as $challenger) {
				$this->_curl->setCountCurl(count($challenger));
				$urlList = array();
				$descriptorArray =& $this->_curl->getDescriptorArray();
				foreach ($descriptorArray as $key => &$descriptor) {
					$this->_curl->setOption($descriptor, CURLOPT_PROXY, $challenger[$key]['proxy']);
					$urlList[] = $url;
				}
				foreach ($this->_curl->getContent($urlList) as $key => $answer) {
					$infoProxy = $this->genProxyInfo($challenger[$key], $answer, $descriptorArray[$key]['info']);
					if ($infoProxy) {
						$goodProxy[] = $infoProxy;
					}
				}
			}
			if (count($goodProxy)) return $goodProxy;
		}
		return false;
	}

	private function checkProxyArrayToSite($arrayProxy, $url, $checkWord) {
		if (!is_array($arrayProxy)) return false;
		$goodProxy = array();
		$this->_curl->setCountStream(1);
		$this->_curl->setUseProxy(true);
		$this->_curl->setTypeContent('text');
		$this->_curl->setDefaultOption(CURLOPT_POST, false);
		$this->_curl->setCheckAnswer(false);
		foreach (array_chunk($arrayProxy, 100) as $challenger) {
			$this->_curl->setCountCurl(count($challenger));
			$descriptorArray =& $this->_curl->getDescriptorArray();
			$urlList = array();
			foreach ($descriptorArray as $key => &$descriptor) {
				$this->_curl->setOption($descriptor, CURLOPT_PROXY, $challenger[$key]['proxy']);
				$urlList[] = $url;
			}
			foreach ($this->_curl->getContent($urlList) as $key => $answer) {
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
		return count($goodProxy) ? $goodProxy : false;
	}

	/**
	 * Переделать или делать запрос на другой сервис
	 * @return string
	 */
	public function getServerIp() {
		if (isset($this->_serverIp)) return $this->_serverIp;
		if (false && isset($_SERVER['SERVER_ADDR']) && cStringWork::isIp($_SERVER['SERVER_ADDR'])) {
			$this->_serverIp = $_SERVER['SERVER_ADDR'];
		} else {
			$this->_curl->setUseProxy(false);
			$this->_curl->setCountStream(1);
			$this->_curl->setTypeContent('html');
			$this->_curl->getContent("http://2ip.ru/");
			$answer = $this->_curl->getAnswer();
			$reg = "/<span>\s*Ваш\s*IP\s*адрес:\s*<\/span>\s*<big[^>]*>\s*(?<ip>[^<]*)\s*<\/big>/iUm";
			if (preg_match($reg, $answer[0], $match) && !isset($match['ip']) || !$match['ip'] || !cStringWork::isIp($match['ip'])){
				exit('NO SERVER IP');
			}
			$this->_serverIp = $match['ip'];
		}
		return $this->_serverIp;
	}
} 