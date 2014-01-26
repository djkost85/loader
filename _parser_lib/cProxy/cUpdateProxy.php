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
	private $_dirSource = 'site_source';
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



	function __construct(){
		parent::__construct();
		$this->_curl = new cMultiCurl();
		$this->_curl->setTypeContent('text');
		$this->_curl->setEncodingAnswer(false);
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
		$this->selectList($this->getDefaultNameList());
		$proxyList = $this->downloadProxy();
		$archive = $this->getArchiveProxy();
		$archiveProxyList = array();
		$tmp['source_proxy'] = 'archive';
		$tmp['type_proxy'] = 'http';
		foreach ($archive as $proxy) {
			$tmp['proxy'] = $proxy;
			$archiveProxyList[] = $tmp;
		}
		$newProxy = array();
		foreach ($proxyList['content'] as $proxy) {
			$newProxy[] = $proxy['proxy'];
		}
		$this->saveInArchive($newProxy);

		$oldProxy['content'] = array_merge($proxyList['content'], $archiveProxyList);
		$oldProxy['content'] = $this->getUniqueProxyIp($oldProxy['content']);
		$oldProxy['content'] = $this->checkProxyArray($oldProxy['content']);
		$this->_proxyList = $oldProxy;
		$this->saveProxyList($this->_proxyList);
		return $this->_proxyList;
	}

	public function updateList($nameList, $force = false) {
		if ($nameList == $this->getDefaultListName()) {
			return $this->selectProxyList($this->getDefaultListName());
		} else {
			$allProxy = $this->selectProxyList($this->getDefaultListName());
			$this->selectProxyList($nameList);
		}
		$this->freeProxyList();
		$endTermProxy = time() - $this->_storageTime;
		if (
			(
				isset($this->_proxyList)
				&& is_array($this->_proxyList)
				&& isset($this->_proxyList['content'])
				&& count($this->_proxyList['content'])
				&& $this->_proxyList['time'] > $endTermProxy
				&& !$force
			)
			|| (!isset($this->_proxyList['need_update']) || !$this->_proxyList['need_update'])
		) {
			return $this->_proxyList;
		}
		$this->_proxyList['content'] = $this->getProxyByFunction($allProxy['content'], $this->_proxyList['need_function']);
		$this->_proxyList['content'] = $this->checkProxyArrayToSite($this->_proxyList['content'], $this->_proxyList['url'], $this->_proxyList['check_word']);
		$this->saveProxyList($this->_proxyList);
		return $this->_proxyList;
	}
} 