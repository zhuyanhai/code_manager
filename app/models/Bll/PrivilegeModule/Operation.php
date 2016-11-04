<?php
/**
 * 外部接口
 * 
 * 访问权限 - 所有模块均可访问
 * 
 * 权限模块
 * 
 * 权限操作逻辑 - 处理接口
 * 
 * @package Bll
 * @subpackage Bll_PrivilegeModule
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_PrivilegeModule_Operation
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
     * @staticvar Bll_PrivilegeModule_Operation $instance
     * @return \Bll_PrivilegeModule_Operation
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
     * 删除权限
     * 
     * @param int $id 权限ID
     * @return ResultSet
     */
    public function del($id)
    {
        try {
            if (empty($id)) {
                throw new Exception();
            }
            Dao_CodeManager_Privilege::getManager()->update(array('status' => 1, 'update_time' => time()), 'id=:id', $id);
            return F_Result::build()->success();
        } catch(Exception $e) {
            return F_Result::build()->error('删除失败');
        }
    }
    
    /**
     * 恢复权限
     * 
     * @param int $id 权限ID
     * @return ResultSet
     */
    public function revert($id)
    {
        try {
            if (empty($id)) {
                throw new Exception();
            }
            Dao_CodeManager_Privilege::getManager()->update(array('status' => 0, 'update_time' => time()), 'id=:id', $id);
            return F_Result::build()->success();
        } catch(Exception $e) {
            return F_Result::build()->error('恢复失败');
        }
    }
    
}