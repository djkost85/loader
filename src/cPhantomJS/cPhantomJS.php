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
	private $_phantomFilesPath;
	private $_dirForFile = 'files';
	private $_dirForScript = 'script';
	private $_dirForStorage = 'storage';
	private $_keyStream = 'phantomjs';
	private $_options;
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
	private $_scriptName;
	private $_phantomExePath;
	private $_arguments = array();
	/**
	 * @var cCookie
	 */
	private $_cookie;

	public $userAgent;

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

	private function getPathToPhantomDir($dirName){
		return $this->getPhantomFilesPath() . DIRECTORY_SEPARATOR . $dirName;
	}

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
		return $this->getPathToPhantomDir($this->_dirForFile);
	}

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
		return $this->getPathToPhantomDir($this->_dirForScript);
	}

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
		return $this->getPathToPhantomDir($this->_dirForStorage);
	}

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

	/**
	 * @param mixed $keyStream
	 */
	public function setKeyStream($keyStream) {
		$this->_keyStream = $keyStream;
		$this->_cookie = new cCookie($keyStream);
		$this->setDefaultOption('cookies-file', $this->_cookie->getFilePhantomJSName());
	}

	/**
	 * @return mixed
	 */
	public function getKeyStream() {
		return $this->_keyStream;
	}

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


	function __construct($phantomExePath){
		$this->setPhantomExePath($phantomExePath);
		$this->setPhantomFilesPath(dirname(__FILE__));
		$this->setDefaultOption('local-storage-path', $this->getDirForStorage());
		$this->setKeyStream(microtime(1) . mt_rand());
		$this->userAgent = new cUserAgent('desktop');
	}

	public function renderText($path, $screenWidthPx = 1280, $screenHeightPx = 720){
		return $this->customScript('renderText', array($this->userAgent->getRandomUserAgent(), $path, $screenWidthPx, $screenHeightPx));
	}

	public function sendPost($path, $postStr, $screenWidthPx = 1280, $screenHeightPx = 720){
		return $this->customScript('sendPost', array($this->userAgent->getRandomUserAgent(), $path, $postStr, $screenWidthPx, $screenHeightPx));
	}

	public function renderImage($path, $screenWidthPx = 1280, $screenHeightPx = 720, $formatImg = 'PNG'){
		$data = $this->customScript('renderImage', array($this->userAgent->getRandomUserAgent(), $path, $screenWidthPx, $screenHeightPx, $formatImg));
		$pic = base64_decode($data);
		return $pic;
	}

	public function renderPdf($path, $fileName = 'MyPdf.pdf', $format = 'A4', $orientation = 'portrait', $marginCm = 1){
		return $this->customScript('renderPdf',array($this->userAgent->getRandomUserAgent(), $path, $fileName, $format, $orientation, $marginCm . 'cm'));
	}

	public function customScript($scriptName, $arguments = array()){
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
		foreach($this->getDefaultOptions() as $name => $defaultOption){
			$option = $this->getOption($name) ? $this->getOption($name) : $defaultOption;
			$this->setOption($name, $option);
			if($option){
				$options[] = '--' . $name . '=' . $option;
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
		return "'" . $this->getDirForScript() . DIRECTORY_SEPARATOR . $this->getScriptName() . '.js' . "'";
	}

} 