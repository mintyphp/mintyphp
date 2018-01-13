<?php
/**
 * A Compatibility library with PHP 7 random_bytes function.
 */

namespace {

    if (!function_exists('random_bytes')) {

        function random_bytes($length) {

            $strlen = function($binary_string) {
                if (function_exists('mb_strlen')) {
                    return mb_strlen($binary_string, '8bit');
                }
                return strlen($binary_string);
            };

            $buffer = '';
            $buffer_valid = false;
            if (function_exists('mcrypt_create_iv') && !defined('PHALANGER')) {
                $buffer = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
                if ($buffer) {
                    $buffer_valid = true;
                }
            }
            if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes')) {
                $buffer = openssl_random_pseudo_bytes($length);
                if ($buffer) {
                    $buffer_valid = true;
                }
            }
            if (!$buffer_valid && @is_readable('/dev/urandom')) {
                $f = fopen('/dev/urandom', 'r');
                $read = $strlen($buffer);
                while ($read < $length) {
                    $buffer .= fread($f, $length - $read);
                    $read = $strlen($buffer);
                }
                fclose($f);
                if ($read >= $length) {
                    $buffer_valid = true;
                }
            }
            if (!$buffer_valid || $strlen($buffer) < $length) {
                $bl = $strlen($buffer);
                for ($i = 0; $i < $length; $i++) {
                    if ($i < $bl) {
                        $buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
                    } else {
                        $buffer .= chr(mt_rand(0, 255));
                    }
                }
            }

            return $buffer;
        }

    }
}

