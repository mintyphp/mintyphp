<?php

namespace MindaPHP;

class CurlError extends \Exception {};

class Curl
{
	public static $options = array();
	public static $headers = array();
	
	protected static function exec($method,$url,$data=array()) {

		if (Debugger::$enabled) {
			$time = microtime(true);
		}
		
		$ch = curl_init();
		
		static::setOptions($ch,$method,$url,$data);
		
		$result = curl_exec($ch);
		$status = curl_getinfo ($ch,CURLINFO_HTTP_CODE);
				
		curl_close($ch);
		
		if (Debugger::$enabled) {
			$duration = microtime(true)-$time;
			$options = json_encode(static::$options);
			$headers = json_encode(static::$headers);
			$data = json_encode($data);
			Debugger::add('calls',compact('duration','method','url','data','options','headers','status','result'));
		}
		
		return array($status,$result);
	}
	
	protected static function setOptions($ch,$method,$url,$data) {
		
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
				$url .= $data;
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

