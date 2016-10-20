<?php
/**
 * 缓存 工厂 类
 * 
 * - 负责构建各种缓存对象 memcache file 等
 * 
 * @category F
 * @package F_Cache
 * @author allen <allenifox@163.com>
 */
final class F_Cache
{
    /**
     * 创建 memcache 对象
     * 
     * 针对指定服务
     * 
     * @param string $serviceName 服务名称
     * @return F_Cache_Memcache
     */
    public static function createMemcache($serviceName)
    {
        return F_Cache_Memcache::getInstance($serviceName);
    }

}
