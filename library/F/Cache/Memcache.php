<?php
/**
 * memcache 缓存 类
 * 
 * @category F
 * @package F_Cache
 * @subpackage F_Cache_Memcache
 * @author allen <allenifox@163.com>
 */
final class F_Cache_Memcache extends F_Cache_Abstract
{
    /**
     * 获取某服务的memcache缓存对象实例
     * 
     * 每个服务 $serviceName 都是单例模式
     * 
     * @param string $serviceName
     * @return F_Cache_Memcache
     */
    public static function getInstance($serviceName)
    {
        static $instances = array();
        
        if (!isset($instances[$serviceName])) {
            $instances[$serviceName] = new self();
        }
        
        return $instances[$serviceName];
    }
    
}
