<?php
/**
 * 数据表配置类
 * 
 * 每个数据表必须有,没有就好报错
 * 
 */
final class Dao_User_User_Config
{   
    public static $configs = array(
        //数据行类名
        'rowClassName' => 'Dao_User_User',
        //完整表名
        'tableName'    => 'tbl_user',
        //数据库缩略名,对应 db.cfg.php 配置文件
        'dbShortName'  => 'user',
        //数据表主键字段名
        'primaryKey'   => 'userid',
    );
}

/**
 * tbl_user 数据表类
 * 
 * 用户信息 － 基本信息
 * 
 * @package Dao
 * @subpackage Dao_Sop
 * @author allen <allenifox@163.com>
 */
class Dao_User_User extends Dao_Abstract
{
    /**
     * 判断用户是否被锁定
     * 
     * @return boolean true=锁定用户 false=非锁定用户
     */
   public function isLock()
   {
       if (intval($this->status) === 10) {//用户被锁定
           return true;
       }
       return false;
   }
}