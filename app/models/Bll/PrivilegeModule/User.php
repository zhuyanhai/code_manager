<?php
/**
 * 外部接口
 * 
 * 访问权限 - 所有模块均可访问
 * 
 * 权限模块
 * 
 * 用户权限逻辑 - 处理接口
 * 
 * @package Bll
 * @subpackage Bll_PrivilegeModule
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_PrivilegeModule_User
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
     * @staticvar Bll_PrivilegeModule_User $instance
     * @return \Bll_PrivilegeModule_User
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
     * 为用户添加权限
     * 
     * @param int $userid 用户ID
     * @param array $privilegeIds 权限ID数组
     * @return void
     */
    public function add($userid, $privilegeIds)
    {
        Dao_CodeManager_UserPrivilege::getDelete()->delete('userid=:userid', $userid);
        $dataList = array();
        foreach ($privilegeIds as $privilegeId) {
            array_push($dataList, array(
                'userid'       => $userid,
                'privilege_id' => $privilegeId,
            ));
        }
        Dao_CodeManager_UserPrivilege::getMultiInsert()->insert($dataList, true);
    }
    
}