<?php
/**
 * 缓存 抽象 类
 * 
 * @category F
 * @package F_Cache
 * @subpackage F_Cache_Abstract
 * @author allen <allenifox@163.com>
 */
abstract class F_Cache_Abstract 
{
    /**
     * 获取某服务的缓存对象实例
     * 
     * 每个服务 $serviceName 都是单例模式
     * 
     * @param string $serviceName
     */
    abstract protected static function getInstance($serviceName);
}
