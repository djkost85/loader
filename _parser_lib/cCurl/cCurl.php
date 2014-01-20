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

	protected $_url;
	protected $_answer;

	protected function setAnswer($newAnswer){
		$this->_answer = $newAnswer;
	}

	public abstract function getAnswer();

	public $descriptor;

	public function &getDescriptor() {
		return $this->descriptor;
	}

	protected $_checkAnswer = false;

	public function setCheckAnswer($value) {
		$this->_checkAnswer = $value;
	}

	public function getCheckAnswer() {
		return $this->_checkAnswer;
	}

	protected $_referer = 'http://google.com/';

	/**
	 * @param bool|resource $descriptor
	 * @param               $newReferer
	 */
	public function setReferer(&$descriptor, $newReferer){
		$this->_referer = $newReferer;
		$this->setOption($descriptor, CURLOPT_REFERER, $this->_referer);
	}

	/**
	 * @return mixed
	 */
	public function getReferer() {
		return $this->_referer;
	}

	public $defaultOptions = array(
		CURLOPT_HEADER => true,
		CURLOPT_TIMEOUT => 10,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => false,
		CURLOPT_REFERER => 'http://google.com/',
		CURLOPT_POSTFIELDS => '',
		CURLOPT_POST => false,
		CURLOPT_FRESH_CONNECT => true,
		CURLOPT_FORBID_REUSE => true,
	);

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

	public function getDefaultSetting($option) {
		return $this->defaultOptions[$option];
	}


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

	protected $_maxRepeat = 10;

	/**
	 * @param int $maxRepeat
	 */
	public function setMaxRepeat($maxRepeat) {
		$this->_maxRepeat = $maxRepeat;
	}

	/**
	 * @return int
	 */
	public function getMaxRepeat() {
		return $this->_maxRepeat;
	}

	protected $_numRepeat = 0;

	protected function nextRepeat(){
		$this->setNumRepeat($this->getNumRepeat() + 1);
	}

	protected function endRepeat() {
		$this->setNumRepeat(0);
	}

	protected function repeat() {
		if ($this->getNumRepeat() < $this->getMaxRepeat()) {
			$this->nextRepeat();
			return true;
		} else {
			$this->endRepeat();
			return false;
		}
	}
	/**
	 * @param int $numRepeat
	 */
	public function setNumRepeat($numRepeat) {
		$this->_numRepeat = $numRepeat;
	}

	/**
	 * @return int
	 */
	public function getNumRepeat() {
		return $this->_numRepeat;
	}

	protected $_minSizeAnswer = 1000;

	/**
	 * @param int $minSizeAnswer
	 */
	public function setMinSizeAnswer($minSizeAnswer) {
		$this->_minSizeAnswer = $minSizeAnswer;
	}

	/**
	 * @return int
	 */
	public function getMinSizeAnswer() {
		return $this->_minSizeAnswer;
	}

	/**
	 * Тип получаемых данных
	 * @var mixed
	 * [file] Файл
	 * [img] Изображение
	 * [text] Текст
	 * [html] html страницы
	 */
	protected $_typeContent = 'text';

	public function setTypeContent($typeContent = "text") {
		switch ($typeContent) {
			case 'file':
				$this->_typeContent = 'file';
				$this->setEncodingAnswer(false);
				return true;
				break;
			case 'img':
				$this->_typeContent = 'img';
				$this->setEncodingAnswer(false);
				return true;
				break;
			case 'text':
				$this->_typeContent = 'text';
				$this->setEncodingAnswer(true);
				return true;
				break;
			case 'html':
				$this->_typeContent = 'html';
				$this->setEncodingAnswer(true);
				break;
			default:
				$this->setTypeContent('file');
				break;
		}
		return false;
	}

	public function getTypeContent(){
		return $this->_typeContent;
	}

	protected $_encodingAnswer = true;

	/**
	 * @param boolean $encodingAnswer
	 */
	public function setEncodingAnswer($encodingAnswer) {
		$this->_encodingAnswer = $encodingAnswer;
	}

	/**
	 * @return boolean
	 */
	public function getEncodingAnswer() {
		return $this->_encodingAnswer;
	}
	protected $_encodingName = 'utf-8';

	/**
	 * @param string $encodingName
	 */
	public function setEncodingName($encodingName) {
		$this->_encodingName = $encodingName;
	}

	/**
	 * @return string
	 */
	public function getEncodingName() {
		return $this->_encodingName;
	}

	protected $_encodingAnswerName;

	/**
	 * @param mixed $encodingAnswerName
	 */
	public function setEncodingAnswerName($encodingAnswerName) {
		$this->_encodingAnswerName = $encodingAnswerName;
	}

	/**
	 * @return mixed
	 */
	public function getEncodingAnswerName() {
		return $this->_encodingAnswerName;
	}

	protected $_saveOption = false;

	/**
	 * @param mixed $saveOption
	 */
	public function setSaveOption($saveOption) {
		$this->_saveOption = $saveOption;
	}

	/**
	 * @return mixed
	 */
	public function getSaveOption() {
		return $this->_saveOption;
	}

	protected function reinit(){
		$this->close();
		$this->init();
	}

	protected $_useProxy;

	/**
	 * @param mixed $useProxy
	 */
	public function setUseProxy($useProxy) {
		$this->_useProxy = $this->setProxy($useProxy);

	}

	/**
	 * @return mixed
	 */
	public function getUseProxy() {
		return $this->_useProxy;
	}

	/**
	 * @var cProxy
	 */
	public $proxy;

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
		if ($this->getUseProxy()) {
			if (is_object($this->proxy)) {
				$proxyIp = $this->proxy->getProxy($descriptor['descriptor_key'], cStringWork::getDomainName($descriptor['option'][CURLOPT_URL]));
				if (is_string($proxyIp) && cStringWork::isIp($proxyIp)){
					$this->setOption($descriptor, CURLOPT_PROXY, $proxyIp);
				} else {
					$descriptor['option'][CURLOPT_URL] = false;
				}
			} elseif (is_string($this->proxy)){
				$this->setOption($descriptor, CURLOPT_PROXY, $this->proxy);
			}
		} elseif(isset($descriptor['option'][CURLOPT_PROXY])) {
			unset($descriptor['option'][CURLOPT_PROXY]);
		}
	}

	/**
	 * @var cCookie
	 */
	protected $_cookie;

	private function setOptionCookie(&$descriptor){
		$this->_cookie->setName($this->getUseStaticCookie() ? $this->getStaticCookieFileName() : $descriptor['descriptor_key']);
		$this->setOption($descriptor, CURLOPT_COOKIEJAR, $this->_cookie->getFileCurlName());
		$this->setOption($descriptor, CURLOPT_COOKIEFILE, $this->_cookie->getFileCurlName());
	}

	protected $_useStaticCookie = false;

	/**
	 * @param boolean $useStaticCookie
	 */
	public function setUseStaticCookie($useStaticCookie) {
		$this->_useStaticCookie = (bool)$useStaticCookie;
	}

	/**
	 * @return boolean
	 */
	public function getUseStaticCookie() {
		return $this->_useStaticCookie;
	}

	protected $_staticCookieFileName;

	/**
	 * @param string $staticCookieFileName
	 */
	public function setStaticCookieFileName($staticCookieFileName) {
		$this->setUseStaticCookie(true);
		$this->_staticCookieFileName = $staticCookieFileName;
	}

	/**
	 * @return string
	 */
	public function getStaticCookieFileName() {
		return $this->_staticCookieFileName;
	}

	function __construct(){
		$this->setUserAgentList('desktop');
		$this->setDefaultOption(CURLOPT_USERAGENT, $this->getRandomUserAgent());
		$this->_cookie = new cCookie();
		$this->init();
	}

	function __destruct(){
		$this->_cookie->deleteOldCookieFile(86400);
	}

	public abstract function getContent($url = '', $checkRegEx = '##');

	protected abstract function init();

	protected abstract function exec();

	protected abstract function close();

	public function setOption(&$descriptor, $option, $value = null){
		if ($value === null){
			$descriptor['option'][$option] = $this->getDefaultSetting($option);
		}
		else{
			$descriptor['option'][$option] = $value;
		}
		$this->configOption($descriptor, $option, $descriptor['option'][$option]);
	}

	public function setOptions(&$descriptor, $options = array()){
		foreach ($this->defaultOptions as $keySetting => $value) {
			if (isset($options[$keySetting])){
				$this->setOption($descriptor, $keySetting, $options[$keySetting]);
			}
			elseif(isset($descriptor['option'][$keySetting])) {
				$this->setOption($descriptor,$keySetting,$descriptor['option'][$keySetting]);
			}
			else {
				$this->setOption($descriptor, $keySetting);
			}
		}
		$this->setOptionProxy($descriptor);
		$this->setOptionCookie($descriptor);
		return curl_setopt_array($descriptor['descriptor'], $descriptor['option']);
	}

	protected function configOption(&$descriptor, $option, $value){
		switch ($option) {
			case CURLOPT_POST:
				if ($value != NULL) $descriptor['option'][$option] = (bool)$value;
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
				break;
			case CURLOPT_PROXY:
				$this->_useProxy = true;
				break;
			default:
				break;
		}
	}

	protected function getHeader(&$answer){
		$header = array();
		if($answer){
				while(preg_match("%(?<head>^[^<>]*HTTP/\d+\.\d+.*)(\r\n\r\n|\r\r|\n\n)%Ums",$answer,$data)){
					$header[] = $data['head'];
					$answer = trim(preg_replace('%'.preg_quote($data['head'],'%').'%ims', '', $answer));
				}
		}
		return $header;
	}

	protected function checkAnswerValid($answer, $curlData) {
		if (!$this->checkHttpCode($curlData['http_code'])
			|| ($curlData['size_download'] < $curlData['download_content_length'] && $curlData['download_content_length'] != -1)
			|| $curlData['size_download'] < $this->getMinSizeAnswer()) {
			return false;
		}
		switch ($this->getTypeContent()) {
			case 'file':
				return ($this->mimeType($curlData['content_type'], 'file'));
			case 'img':
				return ($this->mimeType($curlData['content_type'], 'img'));
			case 'html':
				return ($this->mimeType($curlData['content_type'], 'html') && preg_match('%<\s*/\s*(html|body)[^<>]*>%ims', $answer));
			default:
				return true;
		}
	}

	public function mimeType($mime, $type) {
		switch ($type) {
			case 'file':
				return true;
			case 'img':
				return preg_match('%image/(gif|p?jpeg|png|svg\+xml|tiff|vnd\.microsoft\.icon|vnd\.wap\.wbmp)%i', $mime);
			case 'html':
				return (preg_match('%text/html%i', $mime));
			default:
				return true;
		}
	}

	/**
	 * Проверает HTTP код ответа на запрос
	 * @url http://goo.gl/KKiFi
	 * @param int $httpCode
	 * @return bool
	 * @internal в будущем планируется вести лог с ошибками и из этой функции будет записываться ошибки
	 * @internal в запросах и дополнительо будет приниматься решения больше на посылать заросы на текуший URL
	 * @internal Пример: Если вернуло ошибку 500, то не повторять запрос
	 */
	protected function checkHttpCode($httpCode) {
		switch ((int)$httpCode) {
			case 100:
				return false;
			case 101:
				return false;
			case 102:
				return false;
			case 200:
				return true;
			case 201:
				return true;
			case 202:
				return true;
			case 203:
				return true;
			case 204:
				return true;
			case 205:
				return true;
			case 206:
				return true;
			case 207:
				return true;
			case 226:
				return true;
			case 300:
				return false;
			case 301:
				return false;
			case 302:
				return false;
			case 303:
				return false;
			case 304:
				return false;
			case 305:
				return false;
			case 306:
				return false;
			case 307:
				return false;
			case 400:
				return false;
			case 401:
				return false;
			case 402:
				return false;
			case 403:
				return false;
			case 404:
				return false;
			case 405:
				return false;
			case 406:
				return false;
			case 407:
				return false;
			case 408:
				return false;
			case 409:
				return false;
			case 410:
				return false;
			case 411:
				return false;
			case 412:
				return false;
			case 413:
				return false;
			case 414:
				return false;
			case 415:
				return false;
			case 416:
				return false;
			case 417:
				return false;
			case 422:
				return false;
			case 423:
				return false;
			case 424:
				return false;
			case 425:
				return false;
			case 426:
				return false;
			case 428:
				return false;
			case 429:
				return false;
			case 431:
				return false;
			case 449:
				return false;
			case 451:
				return false;
			case 456:
				return false;
			case 499:
				return false;
			case 500:
				return false;
			case 501:
				return false;
			case 502:
				return false;
			case 503:
				return false;
			case 504:
				return false;
			case 505:
				return false;
			case 506:
				return false;
			case 507:
				return false;
			case 508:
				return false;
			case 509:
				return false;
			case 510:
				return false;
			case 511:
				return false;
			default:
				false;
		}
		return false;
	}

	protected function prepareContent($answer) {
		switch ($this->getTypeContent()) {
			case 'file':
				break;
			case 'text':
				$answer = $this->encodingAnswerText($answer);
				break;
			case 'html':
				$answer = $this->encodingAnswerText($answer);
				break;
			default:
				break;
		}
		return $answer;
	}

	protected function encodingAnswerText($text) {
		if ($this->getEncodingAnswer()) {
			$to = $this->getEncodingName();
			$this->setEncodingAnswerName(cStringWork::getEncodingName($text));
			$from = $this->getEncodingAnswerName();
			if ($from != $to){
				$text = iconv($from, $to, $text);
			}
		}
		return $text;
	}
}