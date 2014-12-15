<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 07.10.2014
 * Time: 18:52
 * Project: loader
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace GetContent;


class cHeaderHTTP {

	protected static $redirectCode = array(300,301,302,303,304,305,306,307);

	const TYPE_CONTENT_TEXT = 'text';
	const TYPE_CONTENT_IMG = 'img';
	const TYPE_CONTENT_HTML = 'html';
	const TYPE_CONTENT_FILE = 'file';

	public static function cutHeader(&$answer){
		$header = array();
		if($answer){
			while(preg_match('%(?<head>^[^<>]*HTTP/\d+\.\d+.*)(\r\n\r\n|\r\r|\n\n)%Ums',$answer,$data)){
				$header[] = $data['head'];
				$answer = ltrim(preg_replace('%'.preg_quote($data['head'],'%').'%ims', '', $answer));
			}
		}
		return $header;
	}

	public static function checkMimeType($mime, $type) {
		switch ($type) {
			case self::TYPE_CONTENT_FILE:
				return true;
			case self::TYPE_CONTENT_IMG:
				return preg_match('%image/(gif|p?jpeg|png|svg\+xml|tiff|vnd\.microsoft\.icon|vnd\.wap\.wbmp)%i', $mime);
			case self::TYPE_CONTENT_HTML:
				return (preg_match('%text/html%i', $mime));
			default:
				return true;
		}
	}

	public static function isRedirect($code){
		return in_array($code, self::$redirectCode);
	}

	/**
	 * Проверает HTTP код ответа на запрос
	 * @url http://goo.gl/KKiFi
	 * @param int $code
	 * @return bool
	 * @internal в будущем планируется вести лог с ошибками и из этой функции будет записываться ошибки
	 * @internal в запросах и дополнительо будет приниматься решения больше на посылать заросы на текуший URL
	 * @internal Пример: Если вернуло ошибку 500, то не повторять запрос
	 */
	public static function checkCode($code) {
		switch ((int)$code) {
			case 100:
				return false;
			case 101:
				return false;
			case 102:
				return false;
			case 200:
				return true;
			case 201:
				return true;
			case 202:
				return true;
			case 203:
				return true;
			case 204:
				return true;
			case 205:
				return true;
			case 206:
				return true;
			case 207:
				return true;
			case 226:
				return true;
			case 300:
				return true;
			case 301:
				return true;
			case 302:
				return true;
			case 303:
				return true;
			case 304:
				return true;
			case 305:
				return true;
			case 306:
				return true;
			case 307:
				return true;
			case 400:
				return true;
			case 401:
				return false;
			case 402:
				return false;
			case 403:
				return true;
			case 404:
				return true;
			case 405:
				return true;
			case 406:
				return true;
			case 407:
				return false;
			case 408:
				return false;
			case 409:
				return false;
			case 410:
				return false;
			case 411:
				return false;
			case 412:
				return false;
			case 413:
				return false;
			case 414:
				return false;
			case 415:
				return false;
			case 416:
				return false;
			case 417:
				return false;
			case 422:
				return false;
			case 423:
				return false;
			case 424:
				return false;
			case 425:
				return false;
			case 426:
				return false;
			case 428:
				return false;
			case 429:
				return false;
			case 431:
				return false;
			case 449:
				return false;
			case 451:
				return false;
			case 456:
				return false;
			case 499:
				return false;
			case 500:
				return false;
			case 501:
				return false;
			case 502:
				return false;
			case 503:
				return false;
			case 504:
				return false;
			case 505:
				return false;
			case 506:
				return false;
			case 507:
				return false;
			case 508:
				return false;
			case 509:
				return false;
			case 510:
				return false;
			case 511:
				return false;
			default:
				false;
		}
		return false;
	}

} 