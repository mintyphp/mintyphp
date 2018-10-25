<?php

namespace MindaPHP;

class CurlError extends \Exception
{};

class Curl
{
    public static $options = array();
    public static $headers = array();
    public static $cookies = false;

    public static function navigateCached($expire, $method, $url, $data, $headers, $options)
    {
        return static::callCached($expire, $method, $url, $data, $headers, array_merge($options, array('CURLOPT_FOLLOWLOCATION' => true)));
    }

    public static function callCached($expire, $method, $url, $data, $headers, $options)
    {
        $key = $method . '_' . $url . '_' . json_encode($data) . '_' . json_encode($headers) . '_' . json_encode($options);
        $result = Cache::get($key);
        if ($result) {
            return $result;
        }
        $result = static::call($method, $url, $data, $headers, $options);
        if ($result['status'] == 200) {
            Cache::set($key, $result, $expire);
        }
        return $result;
    }

    public static function navigate($method, $url, $data = '', $headers = array(), $options = array())
    {
        return static::call($method, $url, $data, $headers, array_merge($options, array('CURLOPT_FOLLOWLOCATION' => true)));
    }

    public static function call($method, $url, $data = '', $headers = array(), $options = array())
    {
        if (Debugger::$enabled) {
            $time = microtime(true);
        }

        $ch = curl_init();

        if (static::$cookies) {
            $cookieJar = tempnam(sys_get_temp_dir(), "curl_cookies-");
            if (isset($_SESSION['curl_cookies'])) {
                file_put_contents($cookieJar, $_SESSION['curl_cookies']);
            }
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
        }

        $headers = array_merge(static::$headers, $headers);
        $options = array_merge(static::$options, $options);
        static::setOptions($ch, $method, $url, $data, $headers, $options);

        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $location = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        if (Debugger::$enabled) {
            $timing = array();
            $timing['name_lookup'] = curl_getinfo($ch, CURLINFO_NAMELOOKUP_TIME);
            $timing['connect'] = curl_getinfo($ch, CURLINFO_CONNECT_TIME);
            $timing['pre_transfer'] = curl_getinfo($ch, CURLINFO_PRETRANSFER_TIME);
            $timing['start_transfer'] = curl_getinfo($ch, CURLINFO_STARTTRANSFER_TIME);
            $timing['redirect'] = curl_getinfo($ch, CURLINFO_REDIRECT_TIME);
            $timing['total'] = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        }

        curl_close($ch);

        if (static::$cookies) {
            $_SESSION['curl_cookies'] = file_get_contents($cookieJar);
            unlink($cookieJar);
        } else {
            unset($_SESSION['curl_cookies']);
        }

        list($head, $body) = explode("\r\n\r\n", $result, 2);
        $result = array('status' => $status);
        $result['headers'] = array();
        $result['data'] = $body;
        $result['url'] = $location;

        foreach (explode("\r\n", $head) as $i => $header) {
            if ($i == 0) {
                continue;
            }
            list($key, $value) = explode(': ', $header);
            $result['headers'][$key] = $value;
        }

        if (Debugger::$enabled) {
            $duration = microtime(true) - $time;
            Debugger::add('api_calls', compact('duration', 'method', 'url', 'data', 'options', 'headers', 'status', 'timing', 'result'));
        }

        return $result;
    }

    protected static function setOptions($ch, $method, &$url, &$data, $headers, $options)
    {
        // Set default options
        foreach ($options as $option => $value) {
            curl_setopt($ch, constant(strtoupper($option)), $value);
        }

        $head = array();
        foreach ($headers as $key => $value) {
            $head[] = $key . ': ' . $value;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
        curl_setopt($ch, CURLOPT_HEADER, true);

        if (is_array($data)) {
            $data = http_build_query($data);
        }

        switch (strtoupper($method)) {
            case 'HEAD':
                curl_setopt($ch, CURLOPT_NOBODY, true);
                break;
            case 'GET':
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                if ($data) {
                    $url .= '?' . $data;
                    $data = '';
                }
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
