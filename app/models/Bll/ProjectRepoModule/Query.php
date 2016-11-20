<?php
/**
 * 外部接口
 * 
 * 访问权限 - 所有模块均可访问
 * 
 * 项目仓库模块
 * 
 * 项目仓库查询逻辑 - 处理接口
 * 
 * @package Bll
 * @subpackage Bll_ProjectRepoModule
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_ProjectRepoModule_Query
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
     * @staticvar Bll_ProjectRepoModule_Query $instance
     * @return \Bll_ProjectRepoModule_Query
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
     * 获取项目仓库 - 根据项目仓库ID
     * 
     * @param int $id 项目仓库ID
     * @return ResultSet
     */
    public function getById($id)
    {
        $repoDao = Dao_CodeManager_ProjectRepo::get($id, 'id');
        if ($repoDao) {
            return F_Result::build()->success($repoDao->toArray());
        }
        return F_Result::build()->error();
    }
    
    /**
     * 获取项目仓库 - 根据项目ID和组织ID
     * 
     * @param int $projectId 项目ID
     * @param int $orgId 组织ID
     * @return ResultSet
     */
    public function getByProjectIdAndOrgId($projectId, $orgId)
    {
        $repoDao = Dao_CodeManager_ProjectRepo::getSelect()->where('project_id=:project_id', $projectId)->where('org_id=:org_id', $orgId)->fetchRow();
        if ($repoDao) {
            return F_Result::build()->success($repoDao->toArray());
        }
        return F_Result::build()->error();
    }
    
    /**
     * 获取最新的项目仓库 - 根据项目ID
     * 
     * @param int $projectId 项目ID
     * @return ResultSet
     */
    public function getNewByProjectId($projectId)
    {
        $repoDao = Dao_CodeManager_ProjectRepo::getSelect()->where('project_id=:project_id', $projectId)->order('id desc')->fetchRow();
        if ($repoDao) {
            return F_Result::build()->success($repoDao->toArray());
        }
        return F_Result::build()->error();
    }
    
}