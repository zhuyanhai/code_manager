<?php
/**
 * 代码管理首页
 * 
 */
class Project_CodeController extends Project_AbstractController
{
    /**
     * 首页
     */
    public function indexAction()
    {
        if (Bll_PrivilegeModule_Query::getInstance()->isSuperAdminByUserid($this->loginUserInfo['userid'])) {//超级管理员
            $resultSet = Bll_ProjectCodeModule_Query::getInstance()->getListOfAll();
        } else {
            $projectCodeIdsResultSet = Bll_ProjectCodeModule_UserPrivilege::getInstance()->getProjectCodeIdsByUserid($this->loginUserInfo['userid']);
            if ($projectCodeIdsResultSet->isSuccess() || $projectCodeIdsResultSet->isEmpty()) {
                $resultSet = Bll_ProjectCodeModule_Query::getInstance()->getListByIds($projectCodeIdsResultSet->projectCodeIds);
            } else {
                $resultSet = F_Result::build()->error();
            }
        }
        
        //分支列表
        if ($resultSet->isSuccess()) {
            $this->view->branchList = $resultSet->getResult();
        } else {
            $this->view->branchList = null;
        }
        
        //当前正在使用的分支
        $repoObj = F_Git::open($this->projectInfo['url']);
        $result  = $repoObj->getBranchInfoOfLocal();
        $this->view->currentUseBranch = $result['selected'];
        if (empty($this->view->currentUseBranch)) {
            $this->view->currentUseBranch = 'Branch';
        }
        
        //当前正在使用的分支的commit history 列表
        
        
    }
    
}