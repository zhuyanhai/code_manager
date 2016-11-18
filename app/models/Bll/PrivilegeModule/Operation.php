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
     * 添加权限
     * 
     * @param array $post
     * @return ResultSet
     */
    public function add($post)
    {
        $validateResult = $this->_validate($post);
        if ($validateResult->isError()) {
            return $validateResult;
        }
        
        Dao_CodeManager_Privilege::getManager()->insert(array(
            'parent_id'   => $post['iParentId'],
            'type'        => $post['iType'],
            'menu_type'   => $post['iMenuType'],
            'name'        => $post['sName'],
            'codename'    => $post['sCodeName'],
            'url'         => $post['sUrl'],
            'create_time' => time(),
            'update_time' => time(),
        ));
        
        return F_Result::build()->success();
    }
    
    /**
     * 编辑权限
     * 
     * @param array $post
     * @return ResultSet
     */
    public function edit($post)
    {
        $validateResult = $this->_validate($post);
        if ($validateResult->isError()) {
            return $validateResult;
        }
        
        if (empty($post['iId'])) {
            return F_Result::build()->error('参数错误');
        }
        
        $privilegeDao = Dao_CodeManager_Privilege::get($post['iId'], 'id');
        if (empty($privilegeDao)) {
            return F_Result::build()->error('参数错误');
        }
        
        Dao_CodeManager_Privilege::getManager()->update(array(
            'parent_id'   => $post['iParentId'],
            'type'        => $post['iType'],
            'menu_type'   => $post['iMenuType'],
            'name'        => $post['sName'],
            'codename'    => $post['sCodeName'],
            'url'         => $post['sUrl'],
            'update_time' => time(),
        ), 'id=:id', $privilegeDao->id);
        
        return F_Result::build()->success();
    }
    
    /**
     * 更新权限缓存 - 关于全部权限
     */
    public function updateCacheOfAll()
    {
        Bll_PrivilegeModule_Internal_BuildMenu::getInstance()->getAll(true);
    }
    
    /**
     * 更新权限缓存 - 关于用户的
     */
    public function updateCacheOfUser()
    {
        Bll_HelperModule_Config::getInstance()->set('privilege_of_user_memcache_update', time());
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
    
//--- 以下私有方法
    
    /**
     * 校验
     * 
     * @param array $post
     * @return ResultSet
     * @throws Utils_Validation_Exception
     */
    private function _validate(&$post)
    {
        try{
            //父级ID
            $post['iParentId'] = Utils_Validation::verify('iParentId', $post)->required()->int()->receive();
            
            //权限类型
            $post['iType'] = Utils_Validation::verify('iType', $post)->required()->int()->notZero()->receive();
            
            //菜单类型
            $post['iMenuType'] = Utils_Validation::verify('iMenuType', $post)->required()->int()->notZero()->receive();
            
            //权限名字
            $post['sName'] = Utils_Validation::verify('sName', $post)->required()->receive();
            $post['sName'] = Utils_Validation::filter($post['sName'])->removeHtml()->removeStr()->receive();
            
            if (intval($post['iMenuType']) === 3 && intval($post['iType']) === 1) {
                $post['sUrl'] = Utils_Validation::verify('sUrl', $post)->required()->receive();
                $post['sUrl'] = Utils_Validation::filter($post['sUrl'])->removeHtml()->removeStr()->receive();
            } else {
                $post['sUrl'] = '';
            }
            
            //权限标识
            $post['sCodeName'] = Utils_Validation::verify('sCodeName', $post)->required()->receive();
            $post['sCodeName'] = Utils_Validation::filter($post['sCodeName'])->removeHtml()->removeStr()->receive();

            return F_Result::build()->success();
        } catch(Utils_Validation_Exception $e) {
            switch ($e->errorKey) {
                case 'iParentId':
                    $msg = '请选择所属父级菜单';
                    break;
                case 'iType':
                    $msg = '请选择权限类型';
                    break;
                case 'iMenuType':
                    $msg = '请选择菜单类型';
                    break;
                case 'sName':
                    $msg = '请输入权限名字';
                    break;
                case 'sCodeName':
                    $msg = '请输入权限标识';
                    break;
                case 'sUrl':
                    $msg = '请输入权限链接';
                    break;
                default:
                    $msg = $e->errorKey.' | '.$e->errorMsg;
                    break;
            }
            return F_Result::build()->error($msg);
        }
    }
    
}