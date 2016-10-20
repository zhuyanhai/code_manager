<?php
/**
 * cookie 工具类
 * 
 * @package Utils
 */
final class Utils_Cookie
{
    /**
     * 设置仅服务端 Cookie
     * 
     * @param string $name   COOKIE数组中下标名字
	 * @param string $value  值
	 * @param int    $expire 缓存时间
	 * @param string $mode   时间计算模式
     * @return void
     */
    public static function setOfServer($name, $value, $expire = 0, $mode = 'h', $domain = null)
    {
        self::set($name, $value, $expire, $mode, $domain, true);
    }
    
    /**
     * 设置客户端 Cookie
     * 
     * @param string $name   COOKIE数组中下标名字
	 * @param string $value  值
	 * @param int    $expire 缓存时间
	 * @param string $mode   时间计算模式
     * @param boolean $httponly 是否仅服务端可访问
     * @return void
     */
    public static function set($name, $value, $expire = 0, $mode = 'h', $domain = null, $httponly = false)
    {
        if ($expire > 0) {
			switch ($mode) {
				case 's':
					$time = 1;
					break;
				case 'm':
					$time = 60;
					break;
				case 'h':
					$time = 60 * 60;
					break;
				case 'd':
					$time = 60 * 60 * 24;
					break;
				case 'y':
					$time = 60 * 60 * 24 * 365;
					break;
			}
			$expire = time() + $time * $expire;
		}
        if(is_null($domain)){
            $domain = '.' . COOKIE_DOMAIN;
        }
		setcookie($name, $value, $expire, '/', $domain, false, $httponly);
    }
    
    /**
     * 获取 cookie
     * 
     * @param string $name
     * @return null
     */
    public static function get($name)
    {
        if (isset($_COOKIE[$name])) {
			return $_COOKIE[$name];
		}
		return null;
    }
    
    /**
     * 删除 Cookie
     * 
     * @param string $name COOKIE数组中下标名字
     * @param string $domain
     * @return void
     */
	public static function del($name, $domain = null)
    {
	    if(is_null($domain)){
            $domain = '.' . COOKIE_DOMAIN;
        }
		setcookie($name, '', (time() - 3600) , '/', $domain);
	}
}