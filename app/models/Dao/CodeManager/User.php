<?php
/**
 * 数据表配置类
 * 
 * 每个数据表必须有,没有就好报错
 * 
 */
final class Dao_CodeManager_User_Config
{   
    public static $configs = array(
        //数据行类名
        'rowClassName' => 'Dao_CodeManager_User',
        //完整表名
        'tableName'    => 'tbl_user',
        //数据库缩略名,对应 db.cfg.php 配置文件
        'dbShortName'  => 'code_manager',
        //数据表主键字段名
        'primaryKey'   => 'userid',
    );
}

/**
 * tbl_user 数据表类
 * 
 * 用户信息
 * 
 * @package Dao
 * @subpackage Dao_CodeManager
 * @author allen <allenifox@163.com>
 */
class Dao_CodeManager_User extends Dao_Abstract
{
    /**
     * 判断用户是否被锁定
     * 
     * @return boolean true=锁定用户 false=非锁定用户
     */
   public function ___isLock()
   {
       if (intval($this->status) === 10) {//用户被锁定
           return true;
       }
       return false;
   }
   
   /**
     * 根据用户ID检查是否是超级管理员
     * 
     * @param int $userid 用户ID
     * @return boolean true=是 false=不是
     */
    public function ___isSuperAdmin()
    {
        if (intval($this->userid) === 1001) {
            return true;
        }
        return false;
    }
}