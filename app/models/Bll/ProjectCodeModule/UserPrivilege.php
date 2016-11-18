<?php
/**
 * 外部接口
 * 
 * 访问权限 - 所有模块均可访问
 * 
 * 项目代码模块
 * 
 * 项目代码用户权限逻辑 - 处理接口
 * 
 * @package Bll
 * @subpackage Bll_ProjectCodeModule
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_ProjectCodeModule_UserPrivilege
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
     * @staticvar Bll_ProjectCodeModule_UserPrivilege $instance
     * @return \Bll_ProjectCodeModule_UserPrivilege
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
        'apply',//申请 - 合并、分发
        'del',//删除
        'merge',//合并
        'create',//创建
        'pack',//打包 - 压缩包
        'tag',//打 tag
        'deploy',//部署 - 将代码部署到 206 或 预发布
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
     * 根据用户ID获取用户所拥有的项目代码的ID数组
     * 
     * @return ResultSet 
     */
    public function getProjectCodeIdsByUserid($userid)
    {
        $result = Dao_CodeManager_ProjectCodeUser::getSelect()->fromColumns('project_code_id')->where('userid=?', $userid)->fetchAll();
        if ($result && $result->count() > 0) {
            $ids = Utils_Array::toFlat($result, 'project_code_id');
            return F_Result::build()->success(array('projectCodeIds', $ids));
        }
        
        return F_Result::build()->success();
    }
    
}