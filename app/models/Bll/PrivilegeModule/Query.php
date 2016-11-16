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
     * 获取权限 - 根据权限ID
     * 
     * @param int $id 权限ID
     * @return ResultSet
     */
    public function getById($id)
    {
        $privilegeDao = Dao_CodeManager_Privilege::get($id, 'id');
        if ($privilegeDao) {
            return F_Result::build()->success($privilegeDao->toArray());
        }
        return F_Result::build()->error();
    }
    
    /**
     * 根据用户ID获取用户的菜单
     * 
     * @param int $userid 用户ID
<<<<<<< Updated upstream
     * @param boolean $resetCache
=======
     * @param boolean $resetCache true=重置缓存 false=正常流程
>>>>>>> Stashed changes
     * @return array
     */
    public function getListOfByUserid($userid, $resetCache = false)
    {
        if ($this->isSuperAdminByUserid($userid) || $userid === 0) {//超级管理员
            return Bll_PrivilegeModule_Internal_BuildMenu::getInstance()->getAll($resetCache);
        } else {
            return Bll_PrivilegeModule_Internal_BuildMenu::getInstance()->getByUserid($userid, $resetCache);
        }
    }
    
    /**
     * 获取全部有效菜单
     * 
     * @param int $userid 用户ID，默认0=全部，>0=获取指定用户的全部权限
     * @return ResultSet
     */
    public function getAllOfAdmin($userid = 0)
    {
        $return = array();
        if ($userid === 0) {
            $list = Dao_CodeManager_Privilege::getSelect()->fromColumns('id, parent_id as pid, name, type')->where('status=:status order by id asc', 0)->fetchAll()->toArray();
        } else {
            $userPrivilegeList = Dao_CodeManager_UserPrivilege::getSelect('privilege_id')->where('userid=:userid', $userid)->fetchAll()->toArray();
            if (count($userPrivilegeList) > 0) {
                $list = Dao_CodeManager_Privilege::getSelect()->fromColumns('id, parent_id as pid, name, type')->where('id in(:id) AND status=:status order by id asc', $userPrivilegeList, 0)->fetchAll()->toArray();
            }
        }
        if (count($list) > 0) {
            foreach ($list as $v) {
                array_push($return, array(
                    'id'   => $v['id'],
                    'name' => (($v['___isMenu'])?'[菜单] ':'[操作] ').$v['name'],
                    'pId'  => $v['pid'],
                    'checked' => false,
                ));
            }
        }
        
        return F_Result::build()->success($return);
    }
}