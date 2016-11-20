<?php
/**
 * 外部接口
 * 
 * 访问权限 - 所有模块均可访问
 * 
 * 项目仓库模块
 * 
 * 项目仓库操作逻辑 - 处理接口
 * 
 * @package Bll
 * @subpackage Bll_ProjectRepoModule
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_ProjectRepoModule_Operation
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
     * @staticvar Bll_ProjectRepoModule_Operation $instance
     * @return \Bll_ProjectRepoModule_Operation
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
     * 添加项目仓库
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
        
        F_Config::load('/configs/repo.cfg.php');
        $repoRootPath = F_Config::get('repo.root');
        clearstatcache();
        if (!is_dir($repoRootPath) || !is_writable($repoRootPath)) {//目录不存在 或 不可写
            return F_Result::build()->error('参考根目录未手动创建，请先创建“'.$repoRootPath.'”');
        }
        
        $orgPath = rtrim($repoRootPath, '/') . '/' . $post['sOrgEname'];
        Utils_File::dirCreate($orgPath);
        $repoFilename = $post['sName'].'.git';
        $repoPath = rtrim($orgPath, '/') . '/' . $repoFilename;
        
        $repoId = Dao_CodeManager_ProjectRepo::getManager()->insert(array(
            'name'        => $post['sName'],
            'intro'       => $post['sIntro'],
            'project_id'  => $post['iProjectId'],
            'org_id'      => $post['iOrgId'],
            'repo_path'   => $repoPath,
            'create_time' => time(),
            'update_time' => time(),
        ));
        
        if ($repoId) {//创建git仓库
            try {
                F_Git::create(true, $repoPath);
            } catch(Exception $e) {
                return F_Result::build()->error('仓库创建失败');
            }
        }
        
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
        
        $repoDao = Dao_CodeManager_ProjectRepo::get($post['iId'], 'id');
        if (empty($repoDao)) {
            return F_Result::build()->error('参数错误');
        }
        
        Dao_CodeManager_ProjectRepo::getManager()->update(array(
            'name'  => $post['sName'],
            'intro' => $post['sIntro'],
            'update_time' => time(),
        ), 'id=:id', $repoDao->id);
        
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
            Dao_CodeManager_ProjectRepo::getManager()->update(array('status' => 1, 'update_time' => time()), 'id=:id', $id);
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
            Dao_CodeManager_ProjectRepo::getManager()->update(array('status' => 0, 'update_time' => time()), 'id=:id', $id);
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
            //项目仓库名字
            $post['sName'] = Utils_Validation::verify('sName', $post)->required()->receive();
            $post['sName'] = Utils_Validation::filter($post['sName'])->removeHtml()->removeStr()->receive();
            
            //项目仓库描述
            $post['sIntro'] = Utils_Validation::verify('sIntro', $post)->required()->receive();
            $post['sIntro'] = Utils_Validation::filter($post['sIntro'])->removeHtml()->removeStr()->receive();
            
            //项目ID
            $post['iProjectId'] = Utils_Validation::verify('iProjectId', $post)->required()->int()->notZero()->receive();
            
            //项目仓库所属组织ID
            $post['iOrgId'] = Utils_Validation::verify('iOrgId', $post)->required()->int()->notZero()->receive();
            $orgResultSet   = Bll_AccountModule_Org::getInstance()->getById($post['iOrgId']);
            if ($orgResultSet->isError()) {
                throw new Utils_Validation_Exception('iOrgId');
            }
            $post['sOrgEname'] = $orgResultSet->ename;
            
            //检测仓库是否创建过
            $repoResultSet = Bll_ProjectRepoModule_Query::getInstance()->getByProjectIdAndOrgId($post['iProjectId'], $post['iOrgId']);
            if ($repoResultSet->isSuccess()) {
                throw new Utils_Validation_Exception('repo'); 
            }

            return F_Result::build()->success();
        } catch(Utils_Validation_Exception $e) {
            switch ($e->errorKey) {
                case 'sName':
                    $msg = '请录入仓库名称';
                    break;
                case 'sIntro':
                    $msg = '请录入仓库描述';
                    break;
                case 'iOrgId':
                    $msg = '请选择所属组织';
                    break;
                case 'iProjectId':
                    $msg = '项目不存在';
                    break;
                case 'repo':
                    $msg = '参考已存在，请勿重复创建';
                    break;
                default:
                    $msg = $e->errorKey.' | '.$e->errorMsg;
                    break;
            }
            return F_Result::build()->error($msg);
        }
    }
    
}