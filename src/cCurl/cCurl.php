<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 07.01.14
 * Time: 10:38
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace GetContent;


abstract class cCurl{

	protected $scheme = 'http';
	protected $schemeDefaultPort = array('http' => 80, 'https' => 443);
	protected $url;
	protected $answer;
	protected $referer = '';

	protected $saveOption = true;


	protected $useCookie = false;
	protected $useProxy;
	/**
	 * @var cCookie
	 */
	protected $cookie;
	protected $useStaticCookie = false;
	protected $staticCookieFileName;

	/**
	 * @var string|cProxy
	 */
	public $proxy;
	/**
	 * @var cUserAgent
	 */
	public $userAgent;
	public $descriptor;
	protected $shareDescriptor;
	public $defaultOptions = array(
		CURLOPT_URL => '',
		CURLOPT_HEADER => true,
		CURLOPT_TIMEOUT => 10,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => false,
		CURLOPT_REFERER => '',
		CURLOPT_POST => false,
		CURLOPT_POSTFIELDS => '',
		CURLOPT_PROXY => false,
		CURLOPT_FRESH_CONNECT => false,
		CURLOPT_FORBID_REUSE => false,
		CURLOPT_AUTOREFERER => true,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_COOKIEJAR => false,
		CURLOPT_COOKIEFILE => false,
		CURLOPT_HTTPHEADER => array(),
		CURLOPT_PORT => 80,
		CURLOPT_MAXREDIRS => 25,
	);

	/**
	 * @param boolean $useCookie
	 */
	public function setUseCookie($useCookie) {
		$this->useCookie = $useCookie;
	}

	/**
	 * @return boolean
	 */
	public function getUseCookie() {
		return $this->useCookie;
	}


	protected function setAnswer($newAnswer){
		$this->answer = $newAnswer;
	}

	public abstract function getAnswer();

	public function setUserAgent($userAgent){
		$this->setDefaultOption(CURLOPT_USERAGENT, $userAgent);
	}

	public function &getDescriptor() {
		return $this->descriptor;
	}

	/**
	 * @param bool|resource $descriptor
	 * @param               $newReferer
	 */
	public function setReferer(&$descriptor, $newReferer){
		$this->referer = $newReferer;
		$this->setOption($descriptor, CURLOPT_REFERER, $this->referer);
	}

	/**
	 * @return mixed
	 */
	public function getReferer() {
		return $this->referer;
	}

	/**
	 * @param array $defaultOption
	 */
	public function setDefaultOptions($defaultOption) {
		$this->defaultOptions = $defaultOption;
	}

	public function setDefaultOption($option, $value) {
		$this->defaultOptions[$option] = $value;
	}

	/**
	 * @return array
	 */
	public function getDefaultOptions() {
		return $this->defaultOptions;
	}

	public function getDefaultOption($option) {
		return $this->defaultOptions[$option];
	}

	/**
	 * @param mixed $saveOption
	 */
	public function setSaveOption($saveOption) {
		$this->saveOption = $saveOption;
	}

	/**
	 * @return mixed
	 */
	public function getSaveOption() {
		return $this->saveOption;
	}

	protected function reInit(){
		$this->close();
		$this->init();
	}

	/**
	 * @param mixed $useProxy
	 */
	public function setUseProxy($useProxy) {
		$this->useProxy = $this->setProxy($useProxy);

	}

	/**
	 * @return mixed
	 */
	public function getUseProxy() {
		return $this->useProxy;
	}

	/**
	 * @param bool|string $proxy
	 * @return bool
	 */
	protected function setProxy($proxy) {
		switch ((bool)$proxy) {
			case true:
				if (is_string($proxy)){
					if(cStringWork::isIp($proxy)){
						$this->proxy = $proxy;
					} else {
						$proxy = false;
					}
				} elseif(!is_object($this->proxy)) {
					$this->proxy = new cProxy();
				}
				break;
			case false:
				$proxy = false;
				break;
			default:
				return false;
		}
		return (bool)$proxy;
	}

	/**
	 * @return string|cProxy
	 */
	public function getProxy() {
		return $this->proxy;
	}

	private function setOptionProxy(&$descriptor){
		if (is_object($this->proxy)) {
			$proxy = $this->proxy->getProxy($descriptor['descriptor_key'], $descriptor['option'][CURLOPT_URL]);
			if (is_string($proxy['proxy']) && cStringWork::isIp($proxy['proxy'])){
				$this->setOption($descriptor, CURLOPT_PROXY, $proxy['proxy']);
			} else {
				$descriptor['option'][CURLOPT_URL] = false;
			}
		} elseif (is_string($this->proxy)){
			$this->setOption($descriptor, CURLOPT_PROXY, $this->proxy);
		}
	}

	private function setOptionCookie(&$descriptor){
		$this->cookie->open($descriptor['descriptor_key']);
		$this->setOption($descriptor, CURLOPT_COOKIEJAR, $this->cookie->getFileCurlName());
		$this->setOption($descriptor, CURLOPT_COOKIEFILE, $this->cookie->getFileCurlName());
	}

	protected function setOptionShare(&$descriptor){
		$this->setOption($descriptor, CURLOPT_SHARE, $this->shareDescriptor);
	}

	/**
	 * @param boolean $useStaticCookie
	 */
	public function setUseStaticCookie($useStaticCookie) {
		$this->useStaticCookie = (bool)$useStaticCookie;
	}

	/**
	 * @return boolean
	 */
	public function getUseStaticCookie() {
		return $this->useStaticCookie;
	}

	/**
	 * @param string $staticCookieFileName
	 */
	public function setStaticCookieFileName($staticCookieFileName) {
		$this->setUseStaticCookie(true);
		$this->staticCookieFileName = $staticCookieFileName;
	}

	/**
	 * @return string
	 */
	public function getStaticCookieFileName() {
		return $this->staticCookieFileName;
	}

	function __construct(){
		$this->userAgent = new cUserAgent('desktop');
		$this->setDefaultOption(CURLOPT_USERAGENT, $this->userAgent->getRandomUserAgent());
		$this->cookie = new cCookie();
		$this->shareInit();
		$this->init();
	}

	function __destruct(){
		curl_share_close($this->shareDescriptor);
		$this->cookie->deleteOldCookieFile(3600);
	}

	public abstract function load($url);

	protected abstract function init();

	protected abstract function exec();

	protected abstract function close();

	protected function shareInit(){
		$this->shareDescriptor = curl_share_init();
		curl_share_setopt($this->shareDescriptor, CURLSHOPT_SHARE, CURL_LOCK_DATA_DNS);
	}

	public final function setOption(&$descriptor, $option, $value = null){
		if ($value === null){
			$descriptor['option'][$option] = $this->getDefaultOption($option);
		} else {
			$descriptor['option'][$option] = $value;
		}
		$this->configOption($descriptor, $option, $descriptor['option'][$option]);
		return true;
	}

	public final function setOptions(&$descriptor, $options = array()){
		foreach($options as $keySetting => $value){
			$this->setOption($descriptor, $keySetting, $options[$keySetting]);
		}
		foreach ($this->defaultOptions as $keySetting => $value) {
			if(!isset($descriptor['option'][$keySetting])) {
				$this->setOption($descriptor, $keySetting);
			}
		}
		if ($this->getUseProxy()) {
			$this->setOptionProxy($descriptor);
		} elseif(isset($descriptor['option'][CURLOPT_PROXY])) {
			unset($descriptor['option'][CURLOPT_PROXY]);
		}
		if($this->getUseCookie()){
			$this->setOptionCookie($descriptor);
		}
		$this->setOptionShare($descriptor);
		return curl_setopt_array($descriptor['descriptor'], $descriptor['option']);
	}

	protected function configOption(&$descriptor, $option, $value){
		switch ($option) {
			case CURLOPT_POST:
				if ($value != NULL) {
					$descriptor['option'][$option] = (bool)$value;
				}
				if(!$descriptor['option'][$option] && isset($descriptor['option'][CURLOPT_POSTFIELDS])) {
					unset($descriptor['option'][CURLOPT_POSTFIELDS]);
				}
				break;
			case CURLOPT_POSTFIELDS:
				if (!$value) {
					unset($descriptor['option'][$option]);
					$this->setOption($descriptor, CURLOPT_POST, false);
				} else {
					$this->setOption($descriptor, CURLOPT_POST, true);
				}
				break;
			case CURLOPT_URL:
				if (!preg_match("%^(http|https)://%iUm", $descriptor['option'][$option])) $descriptor['option'][$option] = "http://" . $value;
				$urlInfo = cStringWork::parseUrl($descriptor['option'][$option]);
				if($urlInfo['scheme'] != $this->scheme){
					$this->setScheme($urlInfo['scheme']);
				}
				break;
			case CURLOPT_PROXY:
				if(cStringWork::isIp($value)){
					$this->useProxy = true;
				}
				break;
			default:
				break;
		}
	}

	protected function saveOption(&$descriptorCurl){
		if (!$this->getSaveOption()){
			unset($descriptorCurl['option']);
		}
	}



	protected function setScheme($schemeName){
		$this->scheme = $schemeName;
		if(isset($this->schemeDefaultPort[$schemeName])){
			$this->setDefaultOption(CURLOPT_PORT, $this->schemeDefaultPort[$schemeName]);
		}
	}

	protected function genDescriptorKey(){
		return microtime(1) . mt_rand();
	}
}