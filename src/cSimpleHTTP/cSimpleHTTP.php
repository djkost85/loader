<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 06.10.2014
 * Time: 16:27
 * Project: parser_ge
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace GetContent;


class cSimpleHTTP {
	protected $options = array();
	protected $shame = 'http';
	protected $url;
	protected $context;
	protected $useProxy;

	public function setOption($name, $value){
		$methodName = 'set'.ucfirst($name);
		if(method_exists(__CLASS__, $methodName)){
			$this->$methodName($value);
		} else {
			$this->options[$name] = $value;
		}
	}

	public function unSetOption($name){
		unset($this->options[$name]);
	}

	/**
	 * @return string
	 */
	public function getMethod() {
		return $this->options['method'];
	}

	/**
	 * @param string $method
	 */
	public function setMethod($method) {
		switch($method){
			case 'POST' :
				$this->options['method'] = 'POST';
				$this->onPost();
				break;
			default:
				$this->options['method'] = 'GET';
				$this->offPost();
		}
	}

	/**
	 * @return string
	 */
	public function getHeader() {
		return $this->options['header'];
	}

	/**
	 * @param string $header
	 */
	public function setHeader($header) {
		$this->options['header'] = is_array($header)?implode("\r\n",$header):$header;
	}

	/**
	 * @return string
	 */
	public function getContent() {
		return $this->options['content'];
	}

	/**
	 * @param string $content
	 */
	public function setContent($content) {
		$this->options['content'] = is_array($content)?http_build_query($content):$content;
	}

	/**
	 * @return int
	 */
	public function getTimeout() {
		return $this->options['timeout'];
	}

	/**
	 * @param int $timeout
	 */
	public function setTimeout($timeout) {
		$this->options['timeout'] = $timeout;
	}

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
	 * @return string
	 */
	public function getShame() {
		return $this->shame;
	}

	/**
	 * @param string $shame
	 */
	public function setShame($shame) {
		switch($shame){
			case 'https' || 'ssl':
				$this->shame = 'ssl';
				$this->onHTTPS();
				break;
			default:
				$this->shame = 'http';
		}
		$this->shame = $shame;
	}

	/**
	 * @param mixed $useProxy
	 */
	public function setUseProxy($useProxy) {
		$this->useProxy = $this->setProxy($useProxy);
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
						$this->setOption('proxy', 'tcp://'.$proxy);
						//$this->setOption('request_fulluri', true);
					} else {
						$this->setProxy(false);
					}
				}
				break;
			default:
				$this->unSetOption('proxy');
				//$this->unSetOption('request_fulluri');
		}
		return (bool)$proxy;
	}

	/**
	 * @return mixed
	 */
	public function getUseProxy() {
		return $this->useProxy;
	}

	protected function onHTTPS(){
		$this->setOption('verify_peer', false);
	}

	protected function offHTTPS(){
		$this->unSetOption('verify_peer');
	}

	protected function onPost(){
		$this->setOption('content', '');
	}

	protected function offPost(){
		$this->unSetOption('content');
	}

	protected function init(){
		$this->context = stream_context_create(array($this->getShame() => $this->options));
	}


	public function load($url){
		$this->init();
		return file_get_contents($url, false, $this->context);
	}

} 