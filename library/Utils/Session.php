<?php
/**
 * session 工具类
 * 
 * @package Utils
 */
final class Utils_Session
{   
    /**
     * 设置 session
     * 
     * @param string $name   $_SESSION数组中下标名字
	 * @param string $value  值
	 * @param int    $expire 缓存时间[小于等于session自身可缓存的时间],单位:秒 0=直到session自身过期为止
     * @return void
     */
    public static function set($name, $value, $expire = 0)
    {
        $name   = strval($name);
        $expire = intval($expire);
        $_SESSION[$name] = array(
            'val'    => $value,
            'expire' => ($expire > 0)?(time()+$expire):0,
        );
    }
    
    /**
     * 获取 session
     * 
     * @param string $name $_SESSION数组中下标名字
     * @return null | string
     */
    public static function get($name)
    {
        $name = (string)$name;
        if (isset($_SESSION[$name])) {
            $expire = intval($_SESSION[$name]['expire']);
            if ($expire > 0 && time() > $expire) {
                self::del($name);
                return null;
            }
			return $_SESSION[$name]['val'];
		}
		return null;
    }
    
    /**
     * 删除 session
     * 
     * @param string $name $_SESSION数组中下标名字
     * @return void
     */
	public static function del($name)
    {
        $name = (string)$name;
	    if (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
		}
	}
}