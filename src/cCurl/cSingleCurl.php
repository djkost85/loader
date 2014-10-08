<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 12.01.14
 * Time: 17:08
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace GetContent;



class cSingleCurl extends cCurl{

	private $redirectCount;
	private $maxRedirectCount = 10;

	/**
	 * @param mixed $redirectCount
	 */
	public function setRedirectCount($redirectCount) {
		$this->redirectCount = $redirectCount;
	}

	/**
	 * @return mixed
	 */
	public function getRedirectCount() {
		return $this->redirectCount;
	}

	/**
	 * @param mixed $maxRedirectCount
	 */
	public function setMaxRedirectCount($maxRedirectCount) {
		$this->maxRedirectCount = $maxRedirectCount;
	}

	/**
	 * @return mixed
	 */
	public function getMaxRedirectCount() {
		return $this->maxRedirectCount;
	}

	public function setKeyStream($key){
		$descriptor =& $this->getDescriptor();
		$descriptor['descriptor_key'] = $key;
	}

	public function getKeyStream(){
		$descriptor =& $this->getDescriptor();
		return $descriptor['descriptor_key'];
	}

	private function useRedirect(){
		$this->setRedirectCount($this->getRedirectCount()+1);
		return ($this->getRedirectCount()<=$this->getMaxRedirectCount());
	}

	function __construct(){
		parent::__construct();
	}

	public function init(){
		$descriptor =& $this->getDescriptor();
		if (!isset($descriptor['descriptor_key']) || !$descriptor['descriptor_key']){
			$descriptor['descriptor_key'] = $this->genDescriptorKey();
		}
		$descriptor['descriptor'] = curl_init();
	}

	protected function exec(){
		$descriptor =& $this->getDescriptor();
		return curl_exec($descriptor['descriptor']);
	}

	public function close(){
		$descriptor =& $this->getDescriptor();
		curl_close($descriptor['descriptor']);
		unset($descriptor['descriptor']);
		$this->saveOption($descriptor);
	}

	public function load($url){
		$descriptor =& $this->getDescriptor();
		$this->setOption($descriptor, CURLOPT_URL, $url);
		$this->setOptions($descriptor);
		$answer = $this->exec();
		$descriptor['info'] = curl_getinfo($descriptor['descriptor']);
		$descriptor['info']['error'] = curl_error($descriptor['descriptor']);
		$descriptor['info']['header'] = cHeaderHTTP::cutHeader($answer);
		if(cHeaderHTTP::isRedirect($descriptor['info']['http_code'])){
			if($this->useRedirect()){
				$this->setReferer($descriptor, $url);
				$answer = $this->load($descriptor['info']['redirect_url']);
			}
		}
		$this->setRedirectCount(0);
		$this->setAnswer($answer);
		$this->reInit();
		return $this->getAnswer();
	}

	public function getAnswer(){
		return $this->answer;
	}

	public function getInfo(){
		$descriptor = $this->getDescriptor();
		return isset($descriptor['info'])?$descriptor['info']:false;
	}
}