<?php
/**
 * 外部接口
 * 
 * 访问权限 - 所有模块均可访问
 * 
 * 用户模块
 * 
 * 用户管理逻辑 - 处理接口
 * 
 * @package Bll
 * @subpackage Bll_AccountModule
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_AccountModule_User
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
     * @staticvar Bll_AccountModule_User $instance
     * @return \Bll_AccountModule_User
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
    
    /**
     * 添加账号
     * 
     * @param array $post
     * @return ResultSet
     */
    public function add($post)
    {
        try {
            $post['sAccount'] = Utils_Validation::verify('sAccount', $post)->required()->receive();
            $post['sAccount'] = Utils_Validation::filter($post['sAccount'])->removeHtml()->removeStr()->receive();
            
            $post['sPasswd'] = Utils_Validation::verify('sPasswd', $post)->required()->receive();
            $post['sPasswd'] = Utils_Validation::filter($post['sPasswd'])->removeHtml()->removeStr()->receive();
            
            $post['sRealname'] = Utils_Validation::verify('sRealname', $post)->required()->receive();
            $post['sRealname'] = Utils_Validation::filter($post['sRealname'])->removeHtml()->removeStr()->receive();
            
            $post['sContactPhone'] = Utils_Validation::filter($post['sContactPhone'])->removeHtml()->removeStr()->receive();
            
            $post['sContactEmail'] = Utils_Validation::filter($post['sContactEmail'])->removeHtml()->removeStr()->receive();
            
            print_r($post['aPrivilegeNodes']);
            exit;
        } catch(Utils_Validation_Exception $e) {
            switch ($e->errorKey) {
                case "sAccount":
                    $msg = '请输入账号';
                    break;
                default:
                    $msg = '添加失败';
                    break;
            }
            return F_Result::build()->error($msg);
        }
    }
    
}