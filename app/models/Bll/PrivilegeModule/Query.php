<?php
/**
 * 外部接口
 * 
 * 访问权限 - 所有模块均可访问
 * 
 * 权限模块
 * 
 * 权限查询逻辑 - 处理接口
 * 
 * @package Bll
 * @subpackage Bll_PrivilegeModule
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_PrivilegeModule_Query
{
    private function __construct()
    {
        //empty
    }

    /**
     * 单例模式
     * 
     * 获取类的对象实例
     * 
     * @staticvar Bll_PrivilegeModule_Query $instance
     * @return \Bll_PrivilegeModule_Query
     */
    public static function getInstance()
    {
        static $instance = null;
        
        if (is_null($instance)) {
            $instance = new self();
        }
        
        return $instance;
    }
    
    /**
     * 根据用户ID检查是否是超级管理员
     * 
     * @param int $userid 用户ID
     * @return boolean true=是 false=不是
     */
    public function isSuperAdminByUserid($userid)
    {
        if (intval($userid) === 1001) {
            return true;
        }
        return false;
    }
    
    /**
     * 
     */
    public function getListOfByUserid($userid)
    {
        if ($this->isSuperAdminByUserid($userid)) {//超级管理员
            return RyxStore_Logic_Admin_Menu_BuildAll::build();
        } else {
            return RyxStore_Logic_Admin_Menu_BuildUser::build($adminUserid);
        }
    }
}