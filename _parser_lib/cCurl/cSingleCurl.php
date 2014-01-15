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

	private $_redirectCount = 10;

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

	private $_maxRedirectCount;

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

	private function useRedirect(){
		$this->setRedirectCount($this->getRedirectCount()+1);
		return ($this->getRedirectCount()<=$this->getMaxRedirectCount());
	}


	public function init(){
		$descriptor =& $this->getDescriptor();
		if (!isset($descriptor['descriptor_key']) || !$descriptor['descriptor_key']){
			$descriptor['descriptor_key'] = microtime(1) . mt_rand();
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
		if ($this->getUseProxy() && is_object($this->proxy)) {
			$this->proxy->removeAllRentFromKey($descriptor['descriptor_key']);
		}
		unset($descriptor['descriptor']);
		if (!$this->getSaveOption()) unset($descriptor['option']);
	}

	public function getContent($url = '', $checkRegEx = '%%'){
		$descriptor =& $this->getDescriptor();
		do {
			if ($this->getNumRepeat() > 0) $this->reinit();
			$this->setOption($descriptor, CURLOPT_URL, $url);
			$this->setOptions($descriptor);
			$answer = $this->exec();
			$this->setReferer($descriptor, $url);
			$descriptor['info'] = curl_getinfo($descriptor['descriptor']);
			$descriptor['info']['header'] = $this->getHeader($answer);
			if($this->isRedirect()){
				if($this->useRedirect()){
					$answer = $this->getContent($descriptor['info']['redirect_url']);
				} else {
					break;
				}
			}
			$this->setRedirectCount(0);
			$regAnswer = ($checkRegEx && preg_match($checkRegEx, $answer));
			if ((!$this->getCheckAnswer() || $this->checkAnswerValid($answer, $descriptor['info'])) && $regAnswer) {
				$this->endRepeat();
				break;
			} else {
				$answer = false;
				if ($this->getUseProxy() && is_object($this->proxy)) {
					$this->proxy->removeProxyInList($descriptor['option'][CURLOPT_PROXY]);
				}
			}
		} while ($this->repeat());
		$this->setAnswer($this->prepareContent($answer));
		$this->reinit();
		return $this->getAnswer();
	}

	public function getAnswer(){
		return $this->_answer;
	}

	private function isRedirect(){
		return ($this->descriptor['info']['http_code'] == 301 || $this->descriptor['info']['http_code'] == 302);
	}
}