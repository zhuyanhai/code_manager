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
        //主键
        'primaryKey'   => 'userid',
        /**
         * key-value 形式缓存整个数据表 - 行数据
         *
         * - server  参考 application.ini -> resources.cachemanager.{user}
         * - key     格式说明：kv_table_{表名【取消前缀yr_eap 和 _】}_{版本号【便于以后手动改代码刷新缓存，新增字段后上线来不及写脚本刷新时使用，非不得已不要用，影响效率】}_{主键或指定类似主键的唯一字段}
         * - expires 过期时间，参考 application.ini -> resources.cachemanager.user.frontend.options.lifetime 时间为秒，null 是永不过期
         * - field   kv table 使用的字段名
         * - saveFields memcache 中存储需要明确知道的字段，无或空代表整行数据
         * - keyEncrypt key的加密方式，默认=无 例如：md5
         *
         */
//        'memcache' => array(
//            'userid' => array(
//                'server'  => 'user',
//                'key'     => 'table_user_userid_1_%d',
//                'field'   => 'userid',
//                'expires' => null,
//            ),
//            'account' => array(
//                'server'     => 'user',
//                'key'        => 'table_user_account_1_%s',
//                'field'      => 'account',
//                'saveFields' => array('userid'),
//                'expires'    => null,
//            ),
//        ),
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