<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 05.12.13
 * Time: 17:58
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace GetContent;


class cPhantomJS {

	private $url;
	private $info;
	private $answer;
	private $phantomFilesPath;
	private $dirForFile = 'files';
	private $dirForScript = 'script';
	private $dirForStorage = 'storage';
	private $keyStream = 'phantomjs';
	const optCookiesFile = 'cookies-file';
	const optIgnoreSslErrors = 'ignore-ssl-errors';
	const optLoadImages = 'load-images';
	const optLocalStoragePath = 'local-storage-path';
	const optOutputEncoding = 'output-encoding';
	const optProxy = 'proxy';
	const optProxyType = 'proxy-type';
	const optProxyAuth = 'proxy-auth';
	const optLocalToRemoteUrlAccess = 'local-to-remote-url-access';
	private $options;
	private $defaultOptions;
	private $scriptName;
	private $phantomExePath;
	private $arguments = array();
	private $referer = 'http://google.com/';
	/**
	 * @var cCookie
	 */
	private $cookie;
	/**
	 * @var cUserAgent
	 */
	public $userAgent;
	/**
	 * @var bool
	 */
	private $useProxy;
	/**
	 * @var string|cProxy
	 */
	public $proxy;

	/**
	 * @return mixed
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @param mixed $url
	 */
	public function setUrl($url) {
		$this->url = $url;
	}

	/**
	 * @param mixed $answer
	 */
	public function setAnswer($answer) {
		$this->answer = $answer;
	}

	/**
	 * @return mixed
	 */
	public function getAnswer() {
		return $this->answer;
	}

	private function getPathToPhantomDir($dirName){
		return $this->getPhantomFilesPath() . DIRECTORY_SEPARATOR . $dirName;
	}

	/**
	 * @param string $dirForFile
	 */
	public function setDirForFile($dirForFile) {
		$this->dirForFile = $dirForFile;
	}

	/**
	 * @return string
	 */
	public function getDirForFile() {
		return $this->getPathToPhantomDir($this->dirForFile);
	}

	/**
	 * @param string $dirForScript
	 */
	public function setDirForScript($dirForScript) {
		$this->dirForScript = $dirForScript;
	}

	/**
	 * @return string
	 */
	public function getDirForScript() {
		return $this->getPathToPhantomDir($this->dirForScript);
	}

	/**
	 * @param string $dirForStorage
	 */
	public function setDirForStorage($dirForStorage) {
		$this->dirForStorage = $dirForStorage;
	}

	/**
	 * @return string
	 */
	public function getDirForStorage() {
		return $this->getPathToPhantomDir($this->dirForStorage);
	}

	/**
	 * @param mixed $phantomFilesPath
	 */
	public function setPhantomFilesPath($phantomFilesPath) {
		$this->phantomFilesPath = $phantomFilesPath;
	}

	/**
	 * @return mixed
	 */
	public function getPhantomFilesPath() {
		return $this->phantomFilesPath;
	}

	/**
	 * @param mixed $keyStream
	 */
	public function setKeyStream($keyStream) {
		$this->keyStream = $keyStream;
		$this->cookie = new cCookie($keyStream);
		$this->setDefaultOption(self::optCookiesFile, $this->cookie->getFilePhantomJSName());
	}

	/**
	 * @return mixed
	 */
	public function getKeyStream() {
		return $this->keyStream;
	}

	/**
	 * @param mixed $options
	 */
	public function setOptions($options) {
		$this->options = $options;
	}

	/**
	 * @return mixed
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * @param string      $option
	 * @param bool|string $value
	 */
	public function setOption($option, $value) {
		$this->options[$option] = $value;
	}

	/**
	 * @param string $option
	 * @return bool|string
	 */
	public function getOption($option) {
		return isset($this->options[$option]) ? $this->options[$option] : null;
	}

	/**
	 * @param array $defaultOptions
	 */
	public function setDefaultOptions($defaultOptions) {
		$this->defaultOptions = $defaultOptions;
	}

	/**
	 * @return array
	 */
	public function getDefaultOptions() {
		return $this->defaultOptions;
	}

	/**
	 * @param $option
	 * @param $value
	 */
	public function setDefaultOption($option, $value) {
		$this->defaultOptions[$option] = $value;
	}

	/**
	 * @param $option
	 * @return array
	 */
	public function getDefaultOption($option) {
		return $this->defaultOptions[$option];
	}

	/**
	 * @param mixed $name
	 */
	public function setScriptName($name) {
		$this->scriptName = $name;
	}

	/**
	 * @return mixed
	 */
	public function getScriptName() {
		return $this->scriptName;
	}

	/**
	 * @param $phantomExePath
	 */
	public function setPhantomExePath($phantomExePath) {
		$this->phantomExePath = $phantomExePath;
	}

	/**
	 * @return mixed
	 */
	public function getPhantomExePath() {
		return $this->phantomExePath;
	}

	/**
	 * @param array $arguments
	 */
	public function setArguments($arguments) {
		$this->arguments = $arguments;
	}

	/**
	 * @return array
	 */
	public function getArguments() {
		return $this->arguments;
	}

	/**
	 * @param string|bool|int $useProxy 1/0 true/false '123.123.123.123:8080'
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
	 * @param bool|int|string $proxy true/false 1/0 '123.123.123.123:8080'
	 * @param null|string     $type http|socks5
	 * @param null|string     $user
	 * @param null|string     $password
	 * @return bool
	 */
	protected function setProxy($proxy, $type = null, $user = null, $password = null) {
		switch ((bool)$proxy) {
			case true:
				if (is_string($proxy)){
					if(cStringWork::isIp($proxy)){
						$this->proxy['proxy'] = $proxy;
						$this->proxy['type'] = $type;
						$this->proxy['auth'] = $user === null || $password === null ? null : $user.':'.$password;
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
	 * @param string $referer
	 */
	public function setReferer($referer) {
		$this->referer = $referer;
	}

	/**
	 * @return string
	 */
	public function getReferer() {
		return $this->referer;
	}



	function __construct($phantomExePath){
		$this->setDefaultOptions(
			array(
				self::optCookiesFile => null, // /path/to/cookies.txt
				self::optIgnoreSslErrors => 'true',
				self::optLoadImages => 'false',
				self::optLocalStoragePath => $this->getDirForStorage(), // /some/path
				self::optOutputEncoding => 'utf-8',
				self::optProxy => null, // 192.168.1.42:8080
				self::optProxyType => null, // http|socks5|none
				self::optProxyAuth => null, // username:password
				self::optLocalToRemoteUrlAccess => 'true',
			)
		);
		$this->setPhantomExePath($phantomExePath);
		$this->setPhantomFilesPath(dirname(__FILE__));
		$this->setKeyStream(microtime(1) . mt_rand());
		$this->userAgent = new cUserAgent('desktop');
	}

	public function renderText($path, $screenWidthPx = 1280, $screenHeightPx = 720){
		$this->setUrl($path);
		$answer = $this->customScript('renderText', array($this->userAgent->getRandomUserAgent(), $this->getReferer(), 'path' => $path, $screenWidthPx, $screenHeightPx));
		$this->info['header'] = $this->cutHeader($answer);
		return $answer;
	}

	public function load($url){
		$this->setOption(self::optLoadImages,'false');
		return $this->renderText($url);
	}

	public function sendPost($path, $postStr, $screenWidthPx = 1280, $screenHeightPx = 720){
		$this->setUrl($path);
		return $this->customScript('sendPost', array($this->userAgent->getRandomUserAgent(), $this->getReferer(), 'path' => $path, $postStr, $screenWidthPx, $screenHeightPx));
	}

	public function renderImage($path, $screenWidthPx = 1280, $screenHeightPx = 720, $formatImg = 'PNG'){
		$data = $this->customScript('renderImage', array($this->userAgent->getRandomUserAgent(), $this->getReferer(), 'path' => $path, $screenWidthPx, $screenHeightPx, $formatImg));
		$pic = base64_decode($data);
		return $pic;
	}

	public function renderPdf($path, $fileName = 'MyPdf.pdf', $format = 'A4', $orientation = 'portrait', $marginCm = 1){
		return $this->customScript('renderPdf',array($this->userAgent->getRandomUserAgent(), $this->getReferer(), 'path' => $path, $fileName, $format, $orientation, $marginCm . 'cm'));
	}

	public function customScript($scriptName, $arguments = array()){
		$this->setUrl($arguments['path']);
		$this->setArguments($arguments);
		$this->setScriptName($scriptName);
		return $this->exec();
	}

	private function exec(){
		return $this->execCommand($this->createCommand());
	}

	/**
	 * @internal Если зависает на выполнении этой функции ознакомьтесь с Issue https://github.com/ariya/phantomjs/issues/10845 or send me e-mail to <bpteam22@gmail.com>
	 * @param $command
	 * @return string
	 */
	public function execCommand($command){
		$output = array();
		$return_val = null;
		exec($command, $output, $return_val);
		return $output ? implode("\n", $output) : $return_val;
	}

	public function createCommand(){
		return $this->getPhantomExePath() . ' ' . $this->createOptions() . ' ' . $this->createScriptName() . ' ' . $this->createArguments();
	}

	private function createOptions(){
		$options = array();
		$this->setOptionProxy();
		foreach($this->getDefaultOptions() as $name => $defaultOption){
			$option = $this->getOption($name) ? $this->getOption($name) : $defaultOption;
			$this->setOption($name, $option);
			if($option){
				$options[] = '--' . $name . '=' . $option;
			}
		}
		return implode(' ', $options);
	}

	private function setOptionProxy(){
		if ($this->getUseProxy()) {
			if (is_object($this->proxy)) {
				$proxy = $this->proxy->getProxy($this->getKeyStream(), $this->getUrl());
				if (is_string($proxy['proxy']) && cStringWork::isIp($proxy['proxy'])){
					$this->setOption(self::optProxy, $proxy['proxy']);
					$this->setOption(self::optProxyType, $proxy['protocol']);
				}
			} elseif (is_string($this->proxy['proxy'])){
				$this->setOption(self::optProxy, $this->proxy['proxy']);
				$this->setOption(self::optProxyType, $this->proxy['type']);
				$this->setOption(self::optProxyAuth, $this->proxy['auth']);
			}
		} elseif($this->getOption('proxy') !== null) {
			$this->setOption(self::optProxy, null);
			$this->setOption(self::optProxyType, null);
			$this->setOption(self::optProxyAuth, null);
		}
	}

	private function createArguments(){
		return implode(' ', array_map(array($this, 'prepareArgument'),$this->getArguments()));
	}

	private function prepareArgument($argument){
		return escapeshellarg($argument);
	}

	private function createScriptName(){
		return "'" . $this->getDirForScript() . DIRECTORY_SEPARATOR . $this->getScriptName() . '.js' . "'";
	}

	public function getInfo(){
		return $this->info;
	}

	protected function cutHeader(&$answer){
		$header = array();
		if($answer){
			while(preg_match('%^<HEADER>\[\[(?<head>.*?)\]\]</HEADER>%Ums',$answer,$data)){
				$header = explode("\n\n",$data['head']);
				$answer = ltrim(preg_replace('%<HEADER>\[\['.preg_quote($data['head'],'\]\]</HEADER>%ms').'%ims', '', $answer));
			}
		}
		return $header;
	}

} 