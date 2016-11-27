<?php
/**
 * 外部接口
 * 
 * 访问权限 - 所有模块均可访问
 * 
 * 项目代码模块
 * 
 * 项目代码操作逻辑 - 处理接口
 * 
 * @package Bll
 * @subpackage Bll_ProjectCodeModule
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_ProjectCodeModule_Operation
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
     * @staticvar Bll_ProjectCodeModule_Operation $instance
     * @return \Bll_ProjectCodeModule_Operation
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
     * 添加项目代码分支
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
        
        Dao_CodeManager_ProjectCode::getManager()->insert(array(
            'name'        => $post['sName'],
            'intro'       => $post['sIntro'],
            'project_id'  => $post['iProjectId'],
            'type'        => $post['iType'],
            'create_time' => time(),
            'update_time' => time(),
        ));
        
        return F_Result::build()->success();
    }
    
    /**
     * 编辑项目仓库
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
        
        $codeDao = Dao_CodeManager_ProjectCode::get($post['iId'], 'id');
        if (empty($codeDao)) {
            return F_Result::build()->error('参数错误');
        }
        
        Dao_CodeManager_ProjectCode::getManager()->update(array(
            'name'  => $post['sName'],
            'intro' => $post['sIntro'],
            'update_time' => time(),
        ), 'id=:id', $codeDao->id);
        
        return F_Result::build()->success();
    }
    
    /**
     * 删除项目仓库
     * 
     * @param int $id 项目仓库ID
     * @return ResultSet
     */
    public function del($id)
    {
        try {
            if (empty($id)) {
                throw new Exception();
            }
            Dao_CodeManager_ProjectCode::getManager()->update(array('status' => 1, 'update_time' => time()), 'id=:id', $id);
            return F_Result::build()->success();
        } catch(Exception $e) {
            return F_Result::build()->error('删除失败');
        }
    }
    
    /**
     * 恢复项目仓库
     * 
     * @param int $id 项目仓库ID
     * @return ResultSet
     */
    public function revert($id)
    {
        try {
            if (empty($id)) {
                throw new Exception();
            }
            Dao_CodeManager_ProjectCode::getManager()->update(array('status' => 0, 'update_time' => time()), 'id=:id', $id);
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
            //分支名字
            $post['sName'] = Utils_Validation::verify('sName', $post)->required()->receive();
            $post['sName'] = Utils_Validation::filter($post['sName'])->removeHtml()->removeStr()->receive();
            
            //分支描述
            $post['sIntro'] = Utils_Validation::verify('sIntro', $post)->required()->receive();
            $post['sIntro'] = Utils_Validation::filter($post['sIntro'])->removeHtml()->removeStr()->receive();
            
            //项目ID
            $post['iProjectId'] = Utils_Validation::verify('iProjectId', $post)->required()->int()->notZero()->receive();
            
            //分支类型
            $post['iType'] = Utils_Validation::verify('iType', $post)->required()->int()->notZero()->receive();

            return F_Result::build()->success();
        } catch(Utils_Validation_Exception $e) {
            switch ($e->errorKey) {
                case 'sName':
                    $msg = '请录入分支名称';
                    break;
                case 'sIntro':
                    $msg = '请录入分支描述';
                    break;
                case 'iType':
                    $msg = '请选择分支类型';
                    break;
                case 'iProjectId':
                    $msg = '项目不存在';
                    break;
                default:
                    $msg = $e->errorKey.' | '.$e->errorMsg;
                    break;
            }
            return F_Result::build()->error($msg);
        }
    }
    
}