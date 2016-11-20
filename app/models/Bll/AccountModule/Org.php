<?php
/**
 * 外部接口
 * 
 * 访问权限 - 所有模块均可访问
 * 
 * 用户模块
 * 
 * 组织机构逻辑 - 处理接口
 * 
 * @package Bll
 * @subpackage Bll_AccountModule
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_AccountModule_Org
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
     * @staticvar Bll_AccountModule_Org $instance
     * @return \Bll_AccountModule_Org
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
     * 获取组织 - 根据组织ID
     * 
     * @param int $id 组织ID
     * @return ResultSet
     */
    public function getById($id)
    {
        $orgDao = Dao_CodeManager_Org::get($id, 'id');
        if ($orgDao) {
            return F_Result::build()->success($orgDao->toArray());
        }
        return F_Result::build()->error();
    }
    
    /**
     * 获取全部有效的组织列表
     * 
     * @return ResultSet
     */
    public function getListOfAll()
    {
        $list = Dao_CodeManager_Org::getSelect()->where('status=:status', 0)->fetchAll();
        return F_Result::build()->success($list->toArray());
    }
    
}