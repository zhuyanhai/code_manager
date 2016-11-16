<?php
/**
 * 外部接口
 * 
 * 访问权限 - 所有模块均可访问
 * 
 * 项目模块
 * 
 * 项目操作逻辑 - 处理接口
 * 
 * @package Bll
 * @subpackage Bll_ProjectModule
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_ProjectModule_Operation
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
     * @staticvar Bll_ProjectModule_Operation $instance
     * @return \Bll_ProjectModule_Operation
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
     * 添加项目
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
        
        Dao_CodeManager_Project::getManager()->insert(array(
            'name'        => $post['sName'],
            'intro'       => $post['sIntro'],
            'orders'      => floor(microtime(true) * 1000),
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
        
        $projectDao = Dao_CodeManager_Project::get($post['iId'], 'id');
        if (empty($projectDao)) {
            return F_Result::build()->error('参数错误');
        }
        
        Dao_CodeManager_Project::getManager()->update(array(
            'name'  => $post['sName'],
            'intro' => $post['sIntro'],
            'update_time' => time(),
        ), 'id=:id', $projectDao->id);
        
        return F_Result::build()->success();
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
            Dao_CodeManager_Project::getManager()->update(array('status' => 1, 'update_time' => time()), 'id=:id', $id);
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
            Dao_CodeManager_Project::getManager()->update(array('status' => 0, 'update_time' => time()), 'id=:id', $id);
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
            //项目名字
            $post['sName'] = Utils_Validation::verify('sName', $post)->required()->receive();
            $post['sName'] = Utils_Validation::filter($post['sName'])->removeHtml()->removeStr()->receive();
            
            //项目描述
            $post['sIntro'] = Utils_Validation::verify('sIntro', $post)->required()->receive();
            $post['sIntro'] = Utils_Validation::filter($post['sIntro'])->removeHtml()->removeStr()->receive();

            return F_Result::build()->success();
        } catch(Utils_Validation_Exception $e) {
            switch ($e->errorKey) {
                case 'sName':
                    $msg = '请录入项目名称';
                    break;
                case 'sIntro':
                    $msg = '请录入项目描述';
                    break;
                default:
                    $msg = $e->errorKey.' | '.$e->errorMsg;
                    break;
            }
            return F_Result::build()->error($msg);
        }
    }
    
}