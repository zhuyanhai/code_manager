<?php
/**
 * 数据表配置类
 * 
 * 每个数据表必须有,没有就好报错
 * 
 */
final class Dao_CodeManager_Privilege_Config
{   
    public static $configs = array(
        //数据行类名
        'rowClassName' => 'Dao_CodeManager_Privilege',
        //完整表名
        'tableName'    => 'tbl_privilege',
        //数据库缩略名,对应 db.cfg.php 配置文件
        'dbShortName'  => 'code_manager',
        //主键
        'primaryKey'   => 'id',
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
                'fields'     => '*',//判断更新了哪个字段需要处理钩子
                'classUse'   => 'singleton',//static 、 singleton 、 new
                'hookClass'  => 'Bll_PrivilegeModule_Query',
                'hookMethod' => 'getListOfByUserid',
                'hookParams' => array(0, true),
            ),
        ),
    );
}

/**
 * tbl_privilege 数据表类
 * 
 * 用户信息
 * 
 * @package Dao
 * @subpackage Dao_CodeManager
 * @author allen <allenifox@163.com>
 */
class Dao_CodeManager_Privilege extends Dao_Abstract
{
    /**
     * 显示类型格式化后的数据
     * 
     * @return string
     */
    public function ___showMenuType()
    {
        if (!isset($this->menu_type)) {
            return '';
        }
        switch (intval($this->menu_type)) {
            case 1:
                return '模块';
                break;
            case 2:
                return '目录';
                break;
            case 3:
                return '菜单';
                break;
        }
    }
    
    /**
     * 是否是菜单权限
     * 
     * @return boolean true=菜单/权限  false=仅权限
     */
    public function ___isMenu()
    {
        if (!isset($this->type)) {
            return false;
        }
        if (intval($this->type) === 1) {
            return true;
        }
        return false;
    }
}