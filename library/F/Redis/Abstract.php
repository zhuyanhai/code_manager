<?php
/**
 * redis 操作 基类
 * 
 * @category F
 * @package F_Redis
 * @author allen <allenifox@163.com>
 */
abstract class F_Redis_Abstract
{
    /**
     * 获取服务实例（每个服务都是单例模式）
     * 
     * @param string $serviceName
     */
    abstract public static function getServiceInstance($serviceName);
}