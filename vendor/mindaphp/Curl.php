<?php

namespace MindaPHP;

class CurlError extends \Exception {};

class Curl
{
	public static $options = array();
	public static $headers = array();
	public static $cookies = false;
	
	public static function call($method,$url,$data,&$result) {

		if (Debugger::$enabled) {
			$time = microtime(true);
		}
		
		$ch = curl_init();

		if (static::$cookies) {
			$cookieJar = tempnam(sys_get_temp_dir(),"curl_cookies-");
			if (isset($_SESSION['curl_cookies'])) {
				file_put_contents($cookieJar, $_SESSION['curl_cookies']);
			}
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
		}
				
		static::setOptions($ch,$method,$url,$data);
		
		$result = curl_exec($ch);
		$status = curl_getinfo ($ch,CURLINFO_HTTP_CODE);
				
		curl_close($ch);
		
		if (static::$cookies) {
			$_SESSION['curl_cookies'] = file_get_contents($cookieJar);
			unlink($cookieJar);
		} else {
			unset($_SESSION['curl_cookies']);
		}
		
		if (Debugger::$enabled) {
			$duration = microtime(true)-$time;
			$options = static::$options;
			$headers = static::$headers;
			Debugger::add('api_calls',compact('duration','method','url','data','options','headers','status','result'));
		}
		
		return $status;
	}
	
	protected static function setOptions($ch,$method,&$url,&$data) {
		
		// Set default options
		foreach (static::$options as $option => $value) {
			curl_setopt($ch, constant(strtoupper($option)), $value);
		}
		
		$headers = array();
		foreach (static::$headers as $key => $value) {
			$headers[] = $key.': '.$value;
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		if (is_array($data)) {
			$data = http_build_query($data);
		}
		
		switch (strtoupper($method)) {
			case 'HEAD':
				curl_setopt($ch, CURLOPT_NOBODY, true);
				break;
			case 'GET':
				curl_setopt($ch, CURLOPT_HTTPGET, true);
				$url .= '?'.$data;
				$data = '';
				break;
			case 'POST':
				curl_setopt($ch, CURLOPT_POST, true);
				break;
			default:
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		}

		curl_setopt($ch, CURLOPT_URL, $url);
		
		if (!empty($data)) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	}
}

