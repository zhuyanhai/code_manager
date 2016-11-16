<?php
/**
 * 外部接口
 * 
 * 访问权限 - 所有模块均可访问
 * 
 * 项目模块
 * 
 * 项目查询逻辑 - 处理接口
 * 
 * @package Bll
 * @subpackage Bll_ProjectModule
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_ProjectModule_Query
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
     * @staticvar Bll_ProjectModule_Query $instance
     * @return \Bll_ProjectModule_Query
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
     * 获取项目 - 根据项目ID
     * 
     * @param int $id 项目ID
     * @return ResultSet
     */
    public function getById($id)
    {
        $projectDao = Dao_CodeManager_Project::get($id, 'id');
        if ($projectDao) {
            return F_Result::build()->success($projectDao->toArray());
        }
        return F_Result::build()->error();
    }
    
    /**
     * 获取项目分页列表 - 后台使用
     * 
     * @param array $conditions 搜索条件
     * @param int $page 页码
     * @param int $count 每页数量
     * @return ResultSet
     */
    public function getListOfAdmin($conditions, $page, $count = 20)
    {
        $selector = Dao_CodeManager_Project::getSelect();

        if(isset($conditions['searchType']) && !empty($conditions['searchType']) && isset($conditions['searchValue']) && !empty($conditions['searchValue'])){
            if ($conditions['searchType'] === 'name') {
                $selector->where("name like :name%", $conditions['searchValue']);
            } else {
                $selector->where($conditions['searchType'].'=:'.$conditions['searchType'], $conditions['searchValue']);
            }
        }
        
        if (intval($conditions['status']) !== 99) {
            $selector->where('status=:status', $conditions['status']);
        }

        $list = $selector->order('id desc')->fetchAllOfPage($page, $count);

        return F_Result::build()->success($list);
    }
    
    /**
     * 获取项目列表
     * 
     * @return ResultSet
     */
    public function getList()
    {
        $list = Dao_CodeManager_Project::getSelect()->where('status=:status', 0)->order('orders desc')->fetchAll();
        return F_Result::build()->success($list->toArray());
    }
}