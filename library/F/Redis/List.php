<?php
/**
 * redis List 操作 类
 * 
 * @category F
 * @package F_Redis
 * @author allen <allenifox@163.com>
 */
final class F_Redis_List extends F_Redis_Abstract
{
    private function __construct()
    {
        //empty
    }
    
    /**
     * 获取服务实例（每个服务都是单例模式）
     * 
     * @param string $serviceName
     * @return F_Redis_List
     */
    public static function getServiceInstance($serviceName)
    {
        static $instances = array();
        
        if (!isset($instances[$serviceName])) {
            $instances[$serviceName] = new self();
        }
        
        return $instances[$serviceName];
    }
    
    
    
}