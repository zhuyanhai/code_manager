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
     * 获取 select 操作对象
     * 
     * @return \F_Db_Table_Select
     */
    public static function getSelect()
    {
        return self::_getDb()->getSelect();
    }
    
    /**
     * 获取 insert 操作对象
     * 
     * @return \F_Db_Table_Insert
     */
    public static function getInsert()
    {
        return self::_getDb()->getInsert();
    }
    
    /**
     * 获取 update 操作对象
     * 
     * @return \F_Db_Table_Update
     */
    public static function getUpdate()
    {
        return self::_getDb()->getUpdate();
    }
    
    /**
     * 获取数据库操作对象
     * 
     * @return \F_Db
     */
    private static function _getDb()
    {
        $configClassName = get_called_class() . '_Config';
        return F_Db::getInstance()->___initTableConfigs($configClassName::$configs);
    }
    
}