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

	/**
	 * @var cSingleCurl|cMultiCurl|cPhantomJS|cSimpleHTTP
	 */
	public $loader;

	/**
	 * @var cCookie
	 */
	public $cookie;

	private $loaderName;
	private $oldLoadName = false;
	private $key;

	protected $encodingAnswer = false;
	protected $encodingName = 'utf-8';
	protected $encodingAnswerName = false;

	protected $maxRepeat = 10;
	protected $numRepeat = 0;
	protected $minSizeAnswer = 1000;

	protected $sleepTime = 0;

	/**
	 * @var cUserAgent
	 */
	public $userAgent;

	/**
	 * Тип получаемых данных
	 * @var mixed
	 * [file] Файл
	 * [img] Изображение
	 * [text] Текст
	 * [html] html страницы
	 */
	protected $typeContent = cHeaderHTTP::TYPE_CONTENT_TEXT;

	protected $checkAnswer = false;

	public function setCheckAnswer($value) {
		$this->checkAnswer = $value;
	}

	public function getCheckAnswer() {
		return $this->checkAnswer;
	}

	/**
	 * @param string $typeContent text | img | html | file
	 * @return bool
	 */
	public function setTypeContent($typeContent = cHeaderHTTP::TYPE_CONTENT_TEXT) {
		$this->typeContent = $typeContent;
	}

	public function getTypeContent(){
		return $this->typeContent;
	}

	/**
	 * @param string $mode curl | phantom
	 * @return bool
	 */
	public function setLoader($mode) {
		switch($mode){
			case 'cSingleCurl' :
			case 'cMultiCurl':
			case 'cPhantomJS':
			case 'cSimpleHTTP':
				$this->setOldLoadName($this->getLoaderName());
				unset($this->loader);
				$this->loaderName = $mode;
				$mode = '\GetContent\\' . $mode;
				$this->loader = new $mode();
				break;
			default:
				return false;
		}

		return true;
	}

	/**
	 * @return string
	 */
	public function getLoaderName() {
		return $this->loaderName;
	}

	/**
	 * @return string
	 */
	public function getOldLoadName() {
		return $this->oldLoadName;
	}

	/**
	 * @param string $oldLoadName
	 */
	public function setOldLoadName($oldLoadName) {
		$this->oldLoadName = $oldLoadName;
	}

	/**
	 * @param boolean $encodingAnswer
	 */
	public function setEncodingAnswer($encodingAnswer) {
		$this->encodingAnswer = $encodingAnswer;
	}

	/**
	 * @return boolean
	 */
	public function getEncodingAnswer() {
		return $this->encodingAnswer;
	}

	/**
	 * @param string $encodingName
	 */
	public function setEncodingName($encodingName) {
		$this->encodingName = $encodingName;
	}

	/**
	 * @return string
	 */
	public function getEncodingName() {
		return $this->encodingName;
	}

	/**
	 * @param mixed $encodingAnswerName
	 */
	public function setEncodingAnswerName($encodingAnswerName) {
		$this->encodingAnswerName = $encodingAnswerName;
	}

	/**
	 * @return mixed
	 */
	public function getEncodingAnswerName() {
		return $this->encodingAnswerName;
	}

	/**
	 * @param string $key
	 */
	public function setKey($key) {
		$this->key = $key;
		$this->cookie->open($this->getKey());
	}

	/**
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * @param int $maxRepeat
	 */
	public function setMaxRepeat($maxRepeat) {
		$this->maxRepeat = $maxRepeat;
	}

	/**
	 * @return int
	 */
	public function getMaxRepeat() {
		return $this->maxRepeat;
	}

	protected function nextRepeat(){
		$this->setNumRepeat($this->getNumRepeat() + 1);
	}

	public function endRepeat() {
		$this->setNumRepeat(0);
	}

	public function repeat() {
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
		$this->numRepeat = $numRepeat;
	}

	/**
	 * @return int
	 */
	public function getNumRepeat() {
		return $this->numRepeat;
	}

	/**
	 * @param int $minSizeAnswer
	 */
	public function setMinSizeAnswer($minSizeAnswer) {
		$this->minSizeAnswer = $minSizeAnswer;
	}

	/**
	 * @return int
	 */
	public function getMinSizeAnswer() {
		return $this->minSizeAnswer;
	}

	/**
	 * @param int $millisecond 0.000001 of second
	 */
	public function setSleepTime($millisecond) {
		$this->sleepTime = $millisecond;
	}

	/**
	 * @return int
	 */
	public function getSleepTime() {
		return $this->sleepTime;
	}

	protected final function sleep(){
		if($this->getSleepTime()){
			usleep($this->getSleepTime());
		}
	}

	public function moveCookies(){
		$old = $this->getOldLoadName();
		$new = $this->getLoaderName();
		if($new && $old && $this->canMoveCookies($new) && $this->canMoveCookies($old)){
			$name = $old . 'To' . $new;
			if(method_exists($this, $name)) {
				return call_user_func(array($this, $name));
			}
		}
		return false;
	}

	protected function canMoveCookies($name){
		return $name != 'cMultiCurl' && $name != 'cSimpleHTTP';
	}

	protected function canMultiQuery($name){
		return $name == 'cMultiCurl';
	}

	function __construct($loaderName = 'cSimpleHTTP'){
		$this->cookie = new cCookie();
		$this->genKey();
		$this->userAgent = new cUserAgent('desktop');
		$this->setLoader($loaderName);
		$this->setUserAgent($this->userAgent->getRandomUserAgent());
	}

	public function __call($name, $arguments){
		if(method_exists($this->loader, $name)) {
			return call_user_func_array(array($this->loader, $name), $arguments);
		}
		return false;
	}

	public function __get($name){
		if(property_exists($this->loader, $name)){
			return $this->loader->$name;
		}
		return false;
	}

	public function load($url, $checkRegEx = false){
		$url = is_array($url) ? array_map(function($n){return cStringWork::checkUrlProtocol($n);}, $url) : cStringWork::checkUrlProtocol($url);
		return is_array($url) || $this->canMultiQuery($this->getLoaderName()) ? $this->multiQuery($url, $checkRegEx) : $this->singleQuery($url, $checkRegEx);
	}

	protected function singleQuery($url, $checkRegEx = false){
		do {
			$answer = $this->loader->load($url);
			$this->sleep();
			if(!$this->isGoodAnswer($answer, $this->getInfo(), $checkRegEx)){
				$answer = false;
			}
		} while ($this->repeat() && !$answer);
		$this->setReferer($url);
		$this->endRepeat();
		return $this->prepareContent($answer);
	}

	protected function multiQuery($url, $checkRegEx = false){
		if(is_string($url)){
			$url = array($url);
		}
		$activeUrl = array_values($url);
		$goodAnswer = array();
		do {
			$answer = $this->loader->load($activeUrl);
			foreach($answer as $key => &$value){
				if($this->isGoodAnswer($value, $this->loader->getInfo($key), $checkRegEx)){
					unset($activeUrl[$key]);
					$goodAnswer[$key] = $this->prepareContent($value);
				} else {
					$value = false;
				}
			}
			$this->sleep();
		} while($this->repeat() && $activeUrl);
		$this->endRepeat();
		return $goodAnswer;
	}

	public function genKey(){
		$this->setKey(microtime(true).rand());
	}

	private function cSingleCurlTocPgantomJS(){
		$cookies = $this->cookie->fromFileCurl();
		$this->cookie->creates($cookies);
		return $this->cookie->toFilePhantomJS($cookies);
	}

	private function cPgantomJSTocSingleCurl(){
		$cookies = $this->cookie->fromFilePhantomJS();
		$this->cookie->creates($cookies);
		return $this->cookie->toFileCurl($cookies);
	}

	protected function isGoodAnswer($answer, $info, $checkRegEx = false){
		$regAnswer = (!$checkRegEx || ($checkRegEx && preg_match($checkRegEx, $answer)));
		return (!$this->getCheckAnswer() || $this->checkAnswerValid($answer, $info)) && $regAnswer;
	}

	protected function checkAnswerValid($answer, $info = array()) {
		return (!isset($info['http_code']) || cHeaderHTTP::checkCode($info['http_code'])) && $this->checkSizeAnswer($answer) && (!isset($info['content_type']) || $this->checkTypeContent($info['content_type']));
	}

	protected function checkSizeAnswer($answer){
		return strlen($answer) > $this->getMinSizeAnswer();
	}

	protected function checkTypeContent($type){
		switch ($this->getTypeContent()) {
			case cHeaderHTTP::TYPE_CONTENT_FILE:
				return (cHeaderHTTP::checkMimeType($type, cHeaderHTTP::TYPE_CONTENT_FILE));
			case cHeaderHTTP::TYPE_CONTENT_IMG:
				return (cHeaderHTTP::checkMimeType($type, cHeaderHTTP::TYPE_CONTENT_IMG));
			case cHeaderHTTP::TYPE_CONTENT_HTML:
				return (cHeaderHTTP::checkMimeType($type, cHeaderHTTP::TYPE_CONTENT_HTML));
			default:
				return true;
		}
	}

	protected function prepareContent($answer) {
		switch ($this->getTypeContent()) {
			case cHeaderHTTP::TYPE_CONTENT_TEXT:
				$answer = $this->encodingAnswerText($answer);
				break;
			case cHeaderHTTP::TYPE_CONTENT_HTML:
				$answer = $this->encodingAnswerText($answer);
				break;
		}
		return $answer;
	}

	public function encodingAnswerText($text) {
		if ($this->getEncodingAnswer()) {
			$from = $this->getEncodingAnswerName();
			$to = $this->getEncodingName();
			if(!$from){
				$from = cStringWork::getEncodingName($text);
			}
			if (!preg_match('%'.preg_quote($from,'%').'%i',$to)){
				$text = mb_convert_encoding( $text, $to, $from);
			}
		}
		return $text;
	}


}