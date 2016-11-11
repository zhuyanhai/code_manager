<?php
/**
 * 数据表配置类
 * 
 * 每个数据表必须有,没有就好报错
 * 
 */
final class Dao_CodeManager_UserPrivilege_Config
{   
    public static $configs = array(
        //数据行类名
        'rowClassName' => 'Dao_CodeManager_UserPrivilege',
        //完整表名
        'tableName'    => 'tbl_user_privilege',
        //数据库缩略名,对应 db.cfg.php 配置文件
        'dbShortName'  => 'code_manager',
        /**
         * 相关缓存钩子配置
         * 
         * 所有使用到表 tbl_privilege 的memcache业务类，都需要在此配置
         * 便于每次有相关数据的修改时进行回调处理
         * 
         * @var array 
         */
        'memcacheHooks' => array(
            array(
                'triggers'   => array('insert', 'update', 'delete'),
                'field'      => '*',
                'hookClass'  => 'Bll_PrivilegeModule_Operation',
                'hookMethod' => 'updateCacheOfUser',
                'hookParams' => array(),
            ),
        ),
    );
}

/**
 * tbl_user_privilege 数据表类
 * 
 * 用户与权限的关联关系
 * 
 * @package Dao
 * @subpackage Dao_CodeManager
 * @author allen <allenifox@163.com>
 */
class Dao_CodeManager_UserPrivilege extends Dao_Abstract
{

}