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

	private $_answer;

	/**
	 * @param mixed $answer
	 */
	public function setAnswer($answer) {
		$this->_answer = $answer;
	}

	/**
	 * @return mixed
	 */
	public function getAnswer() {
		return $this->_answer;
	}

	private function getFullPathInPhantomFiles($dirName){
		return $this->getPhantomFilesPath() . DIRECTORY_SEPARATOR . $dirName . DIRECTORY_SEPARATOR;
	}

	private $_dirForFile = 'files';

	/**
	 * @param string $dirForFile
	 */
	public function setDirForFile($dirForFile) {
		$this->_dirForFile = $dirForFile;
	}

	/**
	 * @return string
	 */
	public function getDirForFile() {
		return $this->getFullPathInPhantomFiles($this->_dirForFile);
	}
	private $_dirForScript = 'script';

	/**
	 * @param string $dirForScript
	 */
	public function setDirForScript($dirForScript) {
		$this->_dirForScript = $dirForScript;
	}

	/**
	 * @return string
	 */
	public function getDirForScript() {
		return $this->getFullPathInPhantomFiles($this->_dirForScript);
	}
	private $_dirForStorage = 'storage';

	/**
	 * @param string $dirForStorage
	 */
	public function setDirForStorage($dirForStorage) {
		$this->_dirForStorage = $dirForStorage;
	}

	/**
	 * @return string
	 */
	public function getDirForStorage() {
		return $this->getFullPathInPhantomFiles($this->_dirForStorage);
	}

	private $_phantomFilesPath;

	/**
	 * @param mixed $phantomFilesPath
	 */
	public function setPhantomFilesPath($phantomFilesPath) {
		$this->_phantomFilesPath = $phantomFilesPath;
	}

	/**
	 * @return mixed
	 */
	public function getPhantomFilesPath() {
		return $this->_phantomFilesPath;
	}

	private $_key = 'phantomjs';

	/**
	 * @param mixed $key
	 */
	public function setKey($key) {
		$this->_key = $key;
	}

	/**
	 * @return mixed
	 */
	public function getKey() {
		return $this->_key;
	}
	private $_options;

	/**
	 * @param mixed $options
	 */
	public function setOptions($options) {
		$this->_options = $options;
	}

	/**
	 * @return mixed
	 */
	public function getOptions() {
		return $this->_options;
	}

	/**
	 * @param string      $option
	 * @param bool|string $value
	 */
	public function setOption($option, $value) {
		$this->_options[$option] = $value;
	}

	/**
	 * @param string $option
	 * @return bool|string
	 */
	public function getOption($option) {
		return isset($this->_options[$option]) ? $this->_options[$option] : null;
	}

	private $_defaultOptions = array(
		'cookies-file' => null, // /path/to/cookies.txt
		'ignore-ssl-errors' => 'true',
		'load-images' => 'true',
		'local-storage-path' => null, // /some/path
		'output-encoding' => 'utf-8',
		'proxy' => null, // 192.168.1.42:8080
		'proxy-type' => null, // http|socks5|none
		'proxy-auth' => null, // username:password
		'local-to-remote-url-access' => 'true',
	);

	/**
	 * @param array $defaultOptions
	 */
	public function setDefaultOptions($defaultOptions) {
		$this->_defaultOptions = $defaultOptions;
	}

	/**
	 * @return array
	 */
	public function getDefaultOptions() {
		return $this->_defaultOptions;
	}

	/**
	 * @param $option
	 * @param $value
	 */
	public function setDefaultOption($option, $value) {
		$this->_defaultOptions[$option] = $value;
	}

	/**
	 * @param $option
	 * @return array
	 */
	public function getDefaultOption($option) {
		return $this->_defaultOptions[$option];
	}

	private $_scriptName;

	/**
	 * @param mixed $name
	 */
	public function setScriptName($name) {
		$this->_scriptName = $name;
	}

	/**
	 * @return mixed
	 */
	public function getScriptName() {
		return $this->_scriptName;
	}

	private $_phantomExePath;

	/**
	 * @param $phantomExePath
	 */
	public function setPhantomExePath($phantomExePath) {
		$this->_phantomExePath = $phantomExePath;
	}

	/**
	 * @return mixed
	 */
	public function getPhantomExePath() {
		return $this->_phantomExePath;
	}

	private $_arguments = array();

	/**
	 * @param array $arguments
	 */
	public function setArguments($arguments) {
		$this->_arguments = $arguments;
	}

	/**
	 * @return array
	 */
	public function getArguments() {
		return $this->_arguments;
	}

	/**
	 * @var cCookie
	 */
	private $_cookie;

	public function setCookieFile($name){
		$this->_cookie = new cCookie($name);
		$this->setDefaultOption('cookies-file', $this->_cookie->getFilePhantomJSName());
	}


	function __construct($phantomExePath){
		$this->setPhantomExePath($phantomExePath);
		$this->setKey(microtime(1) . mt_rand());
		$this->setPhantomFilesPath(dirname(__FILE__));
		$this->setDefaultOption('local-storage-path', $this->getDirForStorage());
		$this->setCookieFile($this->getKey());
	}

	public function renderText($path, $screenWidthPx = 1280, $screenHeightPx = 720){
		$this->setArguments(array($path, $screenWidthPx, $screenHeightPx));
		$this->setScriptName('renderText');
		$data = $this->exec();
		return $data;
	}

	public function renderImage($path, $screenWidthPx = 1280, $screenHeightPx = 720, $formatImg = 'PNG'){
		$this->setArguments(array($path, $screenWidthPx, $screenHeightPx, $formatImg));
		$this->setScriptName('renderImage');
		$data = $this->exec();
		$pic = base64_decode($data);
		return $pic;
	}

	public function renderPdf($path, $fileName = 'MyPdf.pdf', $format = 'A4', $orientation = 'portrait', $marginCm = 1){
		$fileName = $this->getDirForFile() . $fileName;
		$this->setArguments(array($path, $fileName, $format, $orientation, $marginCm . 'cm'));
		$this->setScriptName('renderPdf');
		return $this->exec();
	}

	public function getCookie(){
		$this->setScriptName('getCookie');
		return $this->exec();
	}

	/**
	 * @param string $cookies
	 * @return string
	 */
	public function addCookie($cookies){
		$this->setArguments($cookies);
		$this->setScriptName('addCookie');
		return $this->exec();
	}

	/**
	 * @internal Если зависает на выполнении этой функции ознакомьтесь с Issue https://github.com/ariya/phantomjs/issues/10845
	 * @return string
	 */
	private function exec(){
		$output = array();
		$return_val = null;
		//echo $this->createCommand();
		exec($this->createCommand(), $output, $return_val);
		return $output ? implode("\n", $output) : $return_val;
	}

	private function createCommand(){
		return $this->createPhantomExePath() . ' ' . $this->createOptions() . ' ' . $this->createScriptName() . ' ' . $this->createArguments();
	}

	private function createOptions(){
		$options = array();
		foreach($this->getDefaultOptions() as $name => $defaultOption){
			$option = $this->getOption($name) ? $this->getOption($name) : $defaultOption;
			$this->setOption($name, $option);
			if($option){
				$options[] = '--' . $name . '=' .$option;
			}
		}
		return implode(' ', $options);
	}

	private function createArguments(){
		return implode(' ', array_map(array($this, 'prepareArgument'),$this->getArguments()));
	}

	private function prepareArgument($argument){
		return "'" . escapeshellcmd($argument) . "'";
	}

	private function createScriptName(){
		return "'" . $this->getDirForScript() . $this->getScriptName() . '.js' . "'";
	}

	private function createPhantomExePath(){
		return $this->getPhantomExePath();
	}

	public function test(){
		//header ("Content-type: image/png"); //image/png
		$this->setCookieFile('testCookies');
		//var_dump($this->getCookie());
		/*$this->renderImage('http://sinoptik.ua');
		$this->renderImage('http://vk.com');
		$this->renderImage('http://ya.ru');
		$this->renderImage('http://google.com');
		$this->renderImage('http://market.yandex.ru');
		$this->renderImage('http://ukr.net');*/
		$this->renderImage('http://github.com');
		echo (file_get_contents($this->_cookie->getFilePhantomJSName()));
		//echo $this->renderText('http://test1.ru/test.php');
		//var_dump($this->_cookie->fromFilePhantomJS());
		//echo (file_get_contents($this->_cookie->getFilePhantomJSName()));
	}
} 