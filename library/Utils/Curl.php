<?php
/**
 * Curl 封装工具类
 *
 */
class Utils_Curl
{
    
    const COOKIE_FILE = '/tmp/curl_cookie';
    
    /**
     * 模拟POST请求一个地址
     * 
     * @param string $url 请求的地址
     * @param string $content 内容
     * @param int $timeout 超时时间
     * @return string 返回的请求结果 
     */
    public static function post($url , $content , $timeout=2)
    {
        $cookieFile = self::COOKIE_FILE;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_REFERER,'utan_curl');
        curl_setopt($ch, CURLOPT_TIMEOUT,$timeout); //
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U; Linux x86_64; zh-CN; rv:1.9.2.14) Gecko/20110301 Fedora/3.6.14-1.fc14 Firefox/3.6.14');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        $result = curl_exec($ch);
        if (false === $result) {
//            echo curl_errno($ch).':'.curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }
    
    /**
     * 模拟GET请求一个地址
     *
     * @param string $url 地址
     * @param int $timeout 超时时间
     * @return string 返回的结果
     */
    public static function get($url , $timeout=2, $headers = array())
    {
        $cookieFile = self::COOKIE_FILE;
        $ch = curl_init($url);
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) ;
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT,$timeout);
        curl_setopt($ch, CURLOPT_REFERER,'utan_curl');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U; Linux x86_64; zh-CN; rv:1.9.2.14) Gecko/20110301 Fedora/3.6.14-1.fc14 Firefox/3.6.14');
       	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        $result = curl_exec($ch);
        if (false === $result) {
//            echo curl_errno($ch).':'.curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }
    
    
}