<?php
/**
 * 外部接口
 * 
 * 访问权限 - 所有模块均可访问
 * 
 * 用户模块
 * 
 * 用户账号管理逻辑 - 处理接口
 * 
 * @package Bll
 * @subpackage Bll_UserModule
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_UserModule_Account
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
     * @staticvar Bll_UserModule_Account $instance
     * @return \Bll_UserModule_Account
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
     * 获取后台用户分页列表
     * 
     * @param array $conditions 搜索条件
     * @param int $page 页码
     * @param int $count 每页数量
     * @return F_Pagination
     */
    public function getListOfAdmin($conditions, $page, $count = 20)
    {
        $selector = Dao_CodeManager_User::getSelect();

        if(isset($conditions['searchType']) && !empty($conditions['searchType']) && isset($conditions['searchValue']) && !empty($conditions['searchValue'])){
            if ($conditions['searchType'] === 'realname') {
                $selector->where("realname like :realname%", $conditions['searchValue']);
            } else {
                $selector->where($conditions['searchType'].'=:'.$conditions['searchType'], $conditions['searchValue']);
            }
        }
        
        if (intval($conditions['status']) !== 99) {
            $selector->where('status=:status', $conditions['status']);
        }

        $list = $selector->order('userid desc')->fetchAllOfPage($page, $count);

        return $list;
    }
    
}