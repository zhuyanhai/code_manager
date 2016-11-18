<?php
/**
 * 外部接口
 * 
 * 访问权限 - 所有模块均可访问
 * 
 * 项目代码模块
 * 
 * 项目代码查询逻辑 - 处理接口
 * 
 * @package Bll
 * @subpackage Bll_ProjectCodeModule
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_ProjectCodeModule_Query
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
     * @staticvar Bll_ProjectCodeModule_Query $instance
     * @return \Bll_ProjectCodeModule_Query
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
     * 获取项目分支分页列表 - 根据项目代码ID数组
     * 
     * @param array $ids 项目代码ID数组
     * @return ResultSet
     */
    public function getListByIds($ids)
    {
        $list = Dao_CodeManager_ProjectCode::getSelect()->where('id in(:id)', $ids)->where('status=:status', 0)->fetchAll();
        return F_Result::build()->success($list->toArray());
    }
    
    /**
     * 获取项目分支分页列表
     * 
     * @return ResultSet
     */
    public function getListOfAll()
    {
        $list = Dao_CodeManager_ProjectCode::getSelect()->where('status=:status', 0)->fetchAll();
        return F_Result::build()->success($list->toArray());
    }
    
}