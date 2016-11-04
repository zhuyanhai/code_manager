<?php
/**
 * 抽象类 - 负责把常用的，可以公共提取出来的数据处理逻辑方法写在此类中
 *
 * 例如：获取用户 等的常用方法
 *
 * @author allen <allen@yuorngcorp.com>
 * @package Dao
 */
Abstract class Dao_Abstract extends F_Db_Table_Row
{ 
    /**
     * 获取 get 操作对象
     * 
     * 通过 主键或唯一键 来获取数据
     * 并且如果配置的memcache缓存,将自动使用
     * 
     * @param string $val 字段值
     * @param string $field 字段名字
     * @return \F_Db_Table_Get
     */
    public static function get($val, $field)
    {
        return self::getManager()->get($val, $field);
    }
    
    /**
     * 获取 select 操作对象
     * 
     * @return \F_Db_Table_Select
     */
    public static function getSelect()
    {
        return self::getManager()->getSelect();
    }
    
    /**
     * 获取 db 操作类
     * 
     * @return F_Db
     */
    public static function getManager()
    {
        $configClassName = get_called_class() . '_Config';
        $tableConfig     = $configClassName::$configs;
        return F_Db::getInstance()->___initTableConfigs($tableConfig);
    }
    
}