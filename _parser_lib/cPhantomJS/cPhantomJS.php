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
		return $this->_dirForFile;
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
		return $this->_dirForScript;
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
		return $this->_dirForStorage;
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

	function __construct($phantomExePath){
		$this->setPhantomExePath($phantomExePath);
		$this->setPhantomFilesPath(dirname(__FILE__));
	}

	public function fromUrl($url){

	}

	public function screenShotFromUrl($url){
		//$this->setArguments(array($url, ))
	}

	public function fromFile($fileName){

	}

	public function screenShotFromFile($fileName){

	}

	private function exec(){
		$output = array();
		var_dump($this->createCommand());
		exec($this->createCommand(), $output);
		return $output;
	}

	private function createCommand(){
		return $this->getPhantomExePath() . ' ' . $this->createOptions() . ' ' . $this->createScriptName() . ' ' . $this->createArguments();
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
		return "'" . str_replace('\'','\\\'',$argument) . "'";
	}

	private function createScriptName(){
		return $this->getPhantomFilesPath() . '/' . $this->getDirForScript() . '/' . $this->getScriptName() . '.js';
	}

	public function test(){
		$this->setScriptName('hello');
		var_dump($this->exec());
	}
} 