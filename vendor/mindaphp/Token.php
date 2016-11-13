<?php
namespace MindaPHP;

class Token {

	public static $algorithm = 'HS256';
	public static $secret = false;
	public static $leeway = 5; // 5 seconds
	public static $ttl = 30; // 1/2 minute

	protected static $cache = null;

	protected static function getHeaders() {
		if (function_exists('getallheaders')) {
			$headers = getallheaders();
		} else {
			$headers = array();
			foreach ($_SERVER as $key => $value) if (substr($key, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))))] = $value;
			}
		}
		return $headers;
	}

	protected static function getTokenFromHeaders($headers) {
		$parts = explode(' ',trim($headers['Authorization']),2);
		if (count($parts)!=2) return false;
		if ($parts[0]!='Bearer') return false;
		return $parts[1];
	}

	protected static function getVerifiedClaims($token,$time,$leeway,$ttl,$algorithm,$secret) {
		$algorithms = array('HS256'=>'sha256','HS384'=>'sha384','HS512'=>'sha512');
		if (!isset($algorithms[$algorithm])) return false;
		$hmac = $algorithms[$algorithm];
		$token = explode('.',$token);
		if (count($token)<3) return false;
		$header = json_decode(base64_decode(strtr($token[0],'-_','+/')),true);
		if (!$secret) return false;
		if ($header['typ']!='JWT') return false;
		if ($header['alg']!=$algorithm) return false;
		$signature = bin2hex(base64_decode(strtr($token[2],'-_','+/')));
		if ($signature!=hash_hmac($hmac,"$token[0].$token[1]",$secret)) return false;
		$claims = json_decode(base64_decode(strtr($token[1],'-_','+/')),true);
		if (!$claims) return false;
		if (isset($claims['nbf']) && $time+$leeway<$claims['nbf']) return false;
		if (isset($claims['iat']) && $time+$leeway<$claims['iat']) return false;
		if (isset($claims['exp']) && $time-$leeway>$claims['exp']) return false;
		if (isset($claims['iat']) && !isset($claims['exp'])) {
			if ($time-$leeway>$claims['iat']+$ttl) return false;
		}
		return $claims;
	}

	public static function getClaims($headers = false) {
		if ($headers || static::$cache===null) {
			// get from httponly cookie?
			if (!$headers) $headers = static::getHeaders();
			if (!$headers) return false;
			$token = static::getTokenFromHeaders($headers);
			if (!$token) return false;
			$time = time();
			$leeway = static::$leeway;
			$ttl = static::$ttl;
			$algorithm = static::$algorithm;
			$secret = static::$secret;
			static::$cache = static::getVerifiedClaims($token,$time,$leeway,$ttl,$algorithm,$secret);
		}
		return static::$cache;
	}

	protected static function generateToken($claims,$time,$ttl,$algorithm,$secret) {
		$algorithms = array('HS256'=>'sha256','HS384'=>'sha384','HS512'=>'sha512');
		$header = array();
		$header['typ']='JWT';
		$header['alg']=$algorithm;
		$token = array();
		$token[0] = rtrim(strtr(base64_encode(json_encode((object)$header)),'+/','-_'),'=');
		$claims['iat'] = $time;
		$claims['exp'] = $time + $ttl;
		$token[1] = rtrim(strtr(base64_encode(json_encode((object)$claims)),'+/','-_'),'=');
		if (!isset($algorithms[$algorithm])) return false;
		$hmac = $algorithms[$algorithm];
		$signature = hash_hmac($hmac,"$token[0].$token[1]",$secret,true);
		$token[2] = rtrim(strtr(base64_encode($signature),'+/','-_'),'=');
		return implode('.',$token);
	}

	public static function getToken($claims) {
		$time = time();
		$ttl = static::$ttl;
		$algorithm = static::$algorithm;
		$secret = static::$secret;
		$token = static::generateToken($claims,$time,$ttl,$algorithm,$secret);
		return $token;
	}

}
