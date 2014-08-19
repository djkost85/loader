<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 12.01.14
 * Time: 17:07
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace GetContent;


class cMultiCurl extends cCurl{

	public $descriptorArray;

	private $_countDescriptor;
	private $_countStream = 1;
	private $_countCurl = 1;
	private $_waitExecMSec = 10000;

	public function &getDescriptorArray() {
		return $this->descriptorArray;
	}

	public function setCountCurl($value = 1) {
		if ($this->getCountCurl() != $value) {
			$this->_countCurl = $value;
			$this->setCountDescriptor();
			$this->reInit();
		}
	}

	public function getCountCurl() {
		return $this->_countCurl;
	}

	public function setCountStream($value = 1) {
		if ($this->getCountStream() != $value) {
			$this->_countStream = $value;
			$this->setCountDescriptor();
			$this->reInit();
		}
	}

	public function getCountStream() {
		return $this->_countStream;
	}

	private function setCountDescriptor() {
		$this->_countDescriptor = $this->getCountCurl() * $this->getCountStream();
	}

	private function getCountDescriptor() {
		return $this->_countDescriptor;
	}

	/**
	 * @param int $waitExecMSec
	 */
	public function setWaitExecMSec($waitExecMSec) {
		$this->_waitExecMSec = $waitExecMSec;
	}

	/**
	 * @return int
	 */
	public function getWaitExecMSec() {
		return $this->_waitExecMSec;
	}



	function __construct(){
		$this->defaultOptions[CURLOPT_FOLLOWLOCATION] = true;
		$this->setDefaultOption(CURLOPT_FOLLOWLOCATION, true);
		$this->setDefaultOption(CURLOPT_MAXREDIRS, 10);
		$this->setCountDescriptor();
		parent::__construct();
	}

	public function init(){
		$descriptor =& $this->getDescriptor();
		$descriptorArray =& $this->getDescriptorArray();
		$descriptor['descriptor'] = curl_multi_init();
		if (is_array($descriptorArray) && count($descriptorArray) > $this->getCountDescriptor()) {
			$descriptorArray = array_slice($descriptorArray, 0, $this->getCountDescriptor());
		}
		for ($i = 0; $i < $this->getCountDescriptor(); $i++) {
			if (!isset($descriptorArray[$i]['descriptor_key'])) {
				$descriptorArray[$i]['descriptor_key'] = $this->genDescriptorKey();
			}
			$descriptorArray[$i]['descriptor'] = curl_init();
			curl_multi_add_handle($descriptor['descriptor'], $descriptorArray[$i]['descriptor']);
		}
	}

	protected function exec(){
		$descriptor =& $this->getDescriptor();
		$descriptorArray =& $this->getDescriptorArray();
		do {
			curl_multi_exec($descriptor['descriptor'], $running);
			usleep($this->getWaitExecMSec());
		} while ($running > 0);
		$answer = array();
		foreach ($descriptorArray as $key => $value){
			$answer[$key] = curl_multi_getcontent($descriptorArray[$key]['descriptor']);
		}
		return $answer;
	}

	public function close(){
		$descriptor =& $this->getDescriptor();
		$descriptorArray =& $this->getDescriptorArray();
		foreach ($descriptorArray as $key => &$descriptorCurl) {
			if (isset($descriptorCurl['descriptor'])) {
				if(is_resource($descriptorCurl['descriptor'])){
					if(is_resource($descriptor['descriptor'])){
						curl_multi_remove_handle($descriptor['descriptor'], $descriptorCurl['descriptor']);
					}
					curl_close($descriptorCurl['descriptor']);
				}
				unset($descriptorArray[$key]['descriptor']);
				if (!$this->getSaveOption()){
					unset($descriptorCurl['option']);
				}
			}
		}
		if (isset($descriptor['descriptor']) && is_resource($descriptor['descriptor'])) {
			curl_multi_close($descriptor['descriptor']);
		}
	}

	public function genNewKeyStream(){
		$descriptorArray =& $this->getDescriptorArray();
		foreach ($descriptorArray as &$subDescriptor) {
			$subDescriptor['descriptor_key'] = $this->genDescriptorKey();
		}
	}

	public function load($url = array(), $checkRegEx = false){
		if(is_string($url)){
			$url = array($url);
		}
		$this->_url = array_values($url);
		$goodAnswer = array();
		$countMultiStream = $this->getCountStream();
		do {
			$this->setCountCurl(count($this->_url));
			$descriptorArray =& $this->getDescriptorArray();
			$j = 0;
			$urlDescriptorsLink = array();
			foreach ($this->_url as $keyUrl => $valueUrl) {
				for ($i = 0; $i < $countMultiStream; $i++) {
					$urlDescriptorsLink[$keyUrl][] = $j;
					if (isset($descriptorArray[$j]['descriptor'])) {
						$this->setOption($descriptorArray[$j], CURLOPT_URL, $valueUrl);
					}
					$j++;
				}
			}
			foreach ($descriptorArray as &$value){
				$this->setOptions($value);
			}
			$answer = $this->exec();
			foreach ($answer as $key => &$value) {
				$descriptorArray[$key]['info'] = curl_getinfo($descriptorArray[$key]['descriptor']);
				$descriptorArray[$key]['info']['error'] = curl_error($descriptorArray[$key]['descriptor']);
				$descriptorArray[$key]['info']['header'] = $this->getHeader($value);
				$regAnswer = (!$checkRegEx || ($checkRegEx && preg_match($checkRegEx, $value)));
				if ((!$this->getCheckAnswer() || $this->checkAnswerValid($value, $descriptorArray[$key]['info'])) && $regAnswer) {
					$linkKey = $this->getLinkKey($urlDescriptorsLink, $key);
					unset($this->_url[$linkKey]);
					$descriptorArray[$key]['info']['good_answer'] = true;
					$goodAnswer[$linkKey][] = $this->prepareContent($value);
				} else{
					$descriptorArray[$key]['info']['good_answer'] = false;
					if ($this->getUseProxy() && is_object($this->proxy)) {
						$this->proxy->deleteInList($descriptorArray[$key]['option'][CURLOPT_PROXY]);
					}
				}
			}
			$this->reInit();
			$this->sleep();
			if (!$this->_url) {
				$this->endRepeat();
				break;
			}
		} while ($this->repeat());
		$this->_url = $url;
		$this->setAnswer($goodAnswer);
		return $this->getAnswer();
	}

	private function getLinkKey($links, $key){
		foreach($links as $linkKey => $linkValue){
			if(in_array($key,$linkValue)){
				return $linkKey;
			}
		}
		return false;
	}

	public function getAnswer($getAllAnswer = false){
		if (!$getAllAnswer) {
			$a = array();
			foreach ($this->_answer as $key => $value) $a[$key] = cStringWork::getBiggestString($value);
			return $a;
		} else{
			return $this->_answer;
		}
	}


}