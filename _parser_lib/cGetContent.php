<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 07.01.14
 * Time: 10:38
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace GetContent;


abstract class cGetContent {
	protected $_url;
	public $descriptor;
	public $defaultOption = array(

	);
	protected $_maxRepeat = 10;
	protected $_numRepeat = 0;
	protected $_minSizeAnswer = 1000;
	protected $_typeContent = 'text';
	protected $_encodingAnswer = true;
	protected $_needEncoding = 'utf-8';

	public abstract function getContent($url = '');

	protected abstract function init();

	protected abstract function close();

	public abstract function getAnswer();

	public function setOption(&$descriptor, $option, $value){

	}

	public function setOptions(&$descriptor, $options){

	}

	protected function checkOption($option, $value){

	}

} 