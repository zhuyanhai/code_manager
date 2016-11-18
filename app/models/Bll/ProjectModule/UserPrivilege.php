<?php
/**
 * 外部接口
 * 
 * 访问权限 - 所有模块均可访问
 * 
 * 项目模块
 * 
 * 项目的用户权限逻辑 - 处理接口
 * 
 * @package Bll
 * @subpackage Bll_ProjectModule
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_ProjectModule_UserPrivilege
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
     * @staticvar Bll_ProjectModule_UserPrivilege $instance
     * @return \Bll_ProjectModule_UserPrivilege
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
     * 权限
     * 
     * @var array 
     */
    private $_cfgs = array(
        'query',//查询
    );
    
    /**
     * 获取权限配置
     * 
     * @return array
     */
    public function getCfgs()
    {
        return $this->_cfgs;
    }
    
    /**
     * 根据用户ID获取用户所拥有的项目ID数组
     * 
     * @return ResultSet 
     */
    public function getProjectIdsByUserid($userid)
    {
        $result = Dao_CodeManager_ProjectUser::getSelect()->fromColumns('project_id')->where('userid=?', $userid)->fetchAll();
        if ($result && $result->count() > 0) {
            $ids = Utils_Array::toFlat($result, 'project_id');
            return F_Result::build()->success(array('projectIds', $ids));
        }
        
        return F_Result::build()->success();
    }
    
}