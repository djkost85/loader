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

	public function &getDescriptorArray() {
		return $this->descriptorArray;
	}

	public function setCountCurl($value = 1) {
		if ($this->getCountCurl() != $value) {
			$this->close();
			$this->_countCurl = $value;
			$this->setCountDescriptor();
			$this->init();
		}
	}

	public function getCountCurl() {
		return $this->_countCurl;
	}

	public function setCountStream($value = 1) {
		if ($this->getCountStream() != $value) {
			$this->close();
			$this->_countStream = $value;
			$this->setCountDescriptor();
			$this->init();
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

	function __construct(){
		$this->defaultOptions[CURLOPT_FOLLOWLOCATION] = true;
		$this->defaultOptions[CURLOPT_MAXREDIRS] = 10;
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
			if (!isset($descriptorArray[$i]['descriptor_key'])) $descriptorArray[$i]['descriptor_key'] = microtime(1) . mt_rand();
			$descriptorArray[$i]['descriptor'] = curl_init();
			curl_multi_add_handle($descriptor['descriptor'], $descriptorArray[$i]['descriptor']);
		}
	}

	protected function exec(){
		$descriptor =& $this->getDescriptor();
		$descriptorArray =& $this->getDescriptorArray();
		do {
			curl_multi_exec($descriptor['descriptor'], $running);
			usleep(10);
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
		if (isset($descriptor['descriptor'])) {
			foreach ($descriptorArray as $key => $value) {
				if (isset($descriptorArray[$key]['descriptor'])) {
					@curl_multi_remove_handle($descriptor['descriptor'], $descriptorArray[$key]['descriptor']);
					curl_close($descriptorArray[$key]['descriptor']);
					unset($descriptorArray[$key]['descriptor']);
					if (!$this->getSaveOption()) unset($descriptorArray[$key]['option']);
				}
			}
			curl_multi_close($descriptor['descriptor']);
		}
	}

	public function genNewKeyStream(){
		$descriptorArray =& $this->getDescriptorArray();
		foreach ($descriptorArray as &$subDescriptor) {
			$subDescriptor['descriptor_key'] = microtime(1) . mt_rand();
		}
	}

	public function load($url = array(), $checkRegEx = '##'){
		if(is_string($url)){
			$url = array($url);
		}
		$copyUrl = $url;
		$goodAnswer = array();
		do {
			if ($this->getNumRepeat() > 0) $this->reinit();
			$this->setCountCurl(count($url));
			$descriptorArray =& $this->getDescriptorArray();
			$countMultiStream = $this->getCountStream();
			$j = 0;
			$urlDescriptors = array();
			foreach ($url as $keyUrl => $valueUrl) {
				for ($i = 0; $i < $countMultiStream; $i++) {
					$urlDescriptors[$j] = $keyUrl;
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
				$descriptorArray[$key]['info']['header'] = $this->getHeader($value);
				$keyGoodAnswer = ($urlDescriptors[$key] * $countMultiStream) + $key % $countMultiStream;
				if ($checkRegEx && preg_match($checkRegEx, $value)) $regAnswer = true;
				else $regAnswer = false;
				if ((!$this->getCheckAnswer() || $this->checkAnswerValid($value, $descriptorArray[$key]['info'])) && $regAnswer) {
					unset($url[$urlDescriptors[$key]]);
					$goodAnswer[$keyGoodAnswer] = $this->prepareContent($value);
				} else{
					$value = false;
					if ($this->getUseProxy() && is_object($this->proxy)) {
						$this->proxy->deleteInList($descriptorArray[$key]['option'][CURLOPT_PROXY]);
					}
				}
			}
			if (!$url) {
				$this->endRepeat();
				break;
			}
		} while ($this->repeat());
		$completeAnswer = array();
		$j = 0;
		foreach ($copyUrl as $keyUrl => $valueUrl) {
			for ($i = 0; $i < $countMultiStream; $i++) {
				if (isset($goodAnswer[$j])) $completeAnswer[$keyUrl][$i] = $goodAnswer[$j];
				$j++;
			}
		}
		$this->setAnswer($completeAnswer);
		$this->reinit();
		return $this->getAnswer();
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