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

	private $_redirectCount;
	private $_maxRedirectCount = 10;

	/**
	 * @param mixed $redirectCount
	 */
	public function setRedirectCount($redirectCount) {
		$this->_redirectCount = $redirectCount;
	}

	/**
	 * @return mixed
	 */
	public function getRedirectCount() {
		return $this->_redirectCount;
	}

	/**
	 * @param mixed $maxRedirectCount
	 */
	public function setMaxRedirectCount($maxRedirectCount) {
		$this->_maxRedirectCount = $maxRedirectCount;
	}

	/**
	 * @return mixed
	 */
	public function getMaxRedirectCount() {
		return $this->_maxRedirectCount;
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

	public function load($url = '', $checkRegEx = false){
		$descriptor =& $this->getDescriptor();
		do {
			$this->sleep();
			if ($this->getNumRepeat() > 0) $this->reInit();
			$this->setOption($descriptor, CURLOPT_URL, $url);
			$this->setOptions($descriptor);
			$answer = $this->exec();
			$descriptor['info'] = curl_getinfo($descriptor['descriptor']);
			$descriptor['info']['error'] = curl_error($descriptor['descriptor']);
			$descriptor['info']['header'] = $this->getHeader($answer);
			if($this->isRedirect()){
				if($this->useRedirect()){
					$this->setReferer($descriptor, $url);
					$answer = $this->load($descriptor['info']['redirect_url'], $checkRegEx);
				} else {
					break;
				}
			}
			$this->setRedirectCount(0);
			$regAnswer = (!$checkRegEx || ($checkRegEx && preg_match($checkRegEx, $answer)));
			if ((!$this->getCheckAnswer() || $this->checkAnswerValid($answer, $descriptor['info'])) && $regAnswer) {
				$this->endRepeat();
				break;
			} else {
				$answer = false;
				if ($this->getUseProxy() && is_object($this->proxy) && isset($descriptor['option'][CURLOPT_PROXY])) {
					$this->proxy->deleteInList($descriptor['option'][CURLOPT_PROXY]);
				}
			}
		} while ($this->repeat());
		$this->setReferer($descriptor, $url);
		$this->setAnswer($this->prepareContent($answer));
		$this->reInit();
		return $this->getAnswer();
	}

	public function getAnswer(){
		return $this->_answer;
	}

	private function isRedirect(){
		return in_array($this->descriptor['info']['http_code'], $this->_redirectHttpCode);
	}
}