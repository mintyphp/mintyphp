<?php
namespace MindaPHP;

class Token {

	public static $algorithm = 'HS256';
	public static $secret = 'secretkey';
	public static $leeway = 60;
	public static $ttl = 86400; // 1 day

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
		return explode('.',$parts[1],3);
	}

	protected static function verifyToken($token,$algorithm,$secret) {
		$algorithms(
			'HS256'=>'sha256',
			'HS384'=>'sha384',
			'HS512'=>'sha512',
		);
		if (!isset($algorithms[$algorithm])) return false;
		$hmac = $algorithms[$algorithm];
		if (count($token)<3) return false;
		$header = json_decode(base64_decode($token[0]));
		$signature = bin2hex(base64_decode($token[2]));
		if ($header['typ']!='JWT') return false;
		if ($header['algo']!=$algorithm) return false;
		return $signature==hash_hmac($hmac,"$token[0].$token[1]",$secret);
	}

	protected static function verifyClaims($claims,$time,$leeway,$ttl) {
		if (isset($claims['nbf']) && $time+$leeway<$claims['nbf']) return false;
		if (isset($claims['iat']) && $time+$leeway<$claims['iat']) return false;
		if (isset($claims['iat']) && !isset($claims['exp']) && $time-$leeway>$claims['iat']+$ttl) return false;
		if (isset($claims['exp']) && $time-$leeway>$claims['exp']) return false;
		return true;
	}

	protected static function getVerifiedClaims($token,$time,$leeway,$ttl,$algorithm,$secret) {
		if (!static::verifyToken($token,$algorithm,$secret)) return false;
		$claims = json_decode(base64_decode($token[1]));
		if (!$claims) return false;
		if (!static::verifyClaims($claims,$time,$leeway,$ttl)) return false;
		return $claims;
	}

	public static function getClaims() {
		if (static::$cache===null) {
			// get from httponly cookie?
			$headers = static::getHeaders();
			if (!$headers) return false;
			$token = static::getTokenFromHeaders($headers);
			if (!$token) return false;
			$time = time();
			$leeway = static::$leeway;
			$ttl = static::$ttl;
			$algorithm = static::$algorithm;
			$secret = static::$secret;
			static::$cache = static::getVerifiedClaims($headers,$time,$leeway,$ttl,$algorithm,$secret);
		}
		return static::$cache;
	}

}
