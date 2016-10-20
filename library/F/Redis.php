<?php
/**
 * redis 操作类
 * 
 * @category F
 * @package F_Redis
 * @author allen <allenifox@163.com>
 */
final class F_Redis
{
    private function __construct()
    {
        //empty
    }
    
    /**
     * [键值对]创建操作 字符串型（key-value） 的对象
     * 
     * @param string $serviceName 服务名称（具体针对业务定义的，例如：用户服务、消息服务等）
     * @return F_Redis_String
     */
    public static function createKV($serviceName)
    {
        $instance = F_Redis_String::getServiceInstance($serviceName);
        return $instance;
    }
    
    /**
     * [哈希表]创建操作 Hash型 的对象
     * 
     * @param string $serviceName 服务名称（具体针对业务定义的，例如：用户服务、消息服务等）
     * @return F_Redis_Hash
     */
    public static function createHash($serviceName)
    {
        $instance = F_Redis_Hash::getServiceInstance($serviceName);
        return $instance;
    }
    
    /**
     * [列表]创建操作 List型 的对象
     * 
     * - 可做队列操作
     * 
     * @param string $serviceName 服务名称（具体针对业务定义的，例如：用户服务、消息服务等）
     * @return F_Redis_List
     */
    public static function createList($serviceName)
    {
        $instance = F_Redis_List::getServiceInstance($serviceName);
        return $instance;
    }
    
    /**
     * [集合]创建操作 Set型 的对象
     * 
     * @param string $serviceName 服务名称（具体针对业务定义的，例如：用户服务、消息服务等）
     * @return F_Redis_Set
     */
    public static function createSet($serviceName)
    {
        $instance = F_Redis_Set::getServiceInstance($serviceName);
        return $instance;
    }
    
    /**
     * [有序集合]创建操作 Sorted Set型 的对象
     * 
     * @param string $serviceName 服务名称（具体针对业务定义的，例如：用户服务、消息服务等）
     * @return F_Redis_SortedSet
     */
    public static function createSortedSet($serviceName)
    {
        $instance = F_Redis_SortedSet::getServiceInstance($serviceName);
        return $instance;
    }
}