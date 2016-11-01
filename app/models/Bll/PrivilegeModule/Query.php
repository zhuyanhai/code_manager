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
     * 根据用户ID获取用户的菜单
     * 
     * @param int $userid 用户ID
     * @return array
     */
    public function getListOfByUserid($userid)
    {
        if ($this->isSuperAdminByUserid($userid)) {//超级管理员
            return Bll_PrivilegeModule_Internal_BuildMenu::getInstance()->getAll();
        } else {
            return Bll_PrivilegeModule_Internal_BuildMenu::getInstance()->getByUserid($userid);
        }
    }
    
    /**
     * 获取全部有效菜单
     * 
     * @param int $userid 用户ID，默认0=全部，>0=获取指定用户的全部权限
     * @return array
     */
    public function getAllOfAdmin($userid = 0)
    {
        $return = array();
        if ($userid === 0) {
            $list = Dao_CodeManager_Privilege::getSelect()->fromColumns('id, parent_id as pid, name, type')->where('status=:status order by id asc', 0)->fetchAll()->toArray();
        } else {
            $userPrivilegeList = Dao_CodeManager_UserPrivilege::getSelect('privilege_id')->where('userid=:userid', $userid)->fetchAll()->toArray();
            if (count($userPrivilegeList) > 0) {
                $list = Dao_CodeManager_Privilege::getSelect()->fromColumns('id, parent_id as pid, name, type')->where('id in(:id) AND status=:status order by id asc', implode(',', $userPrivilegeList), 0)->fetchAll()->toArray();
            }
        }
        if (count($list) > 0) {
            foreach ($list as $v) {
                array_push($return, array(
                    'id'   => $v['id'],
                    'name' => (($v['___isMenu'])?'[菜单] ':'[操作] ').$v['name'],
                    'pId'  => $v['pid'],
                ));
            }
        }
        
        return $return;
    }
}