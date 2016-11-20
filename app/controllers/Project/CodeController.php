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
        if ($this->loginUserInfo['___isSuperAdmin']) {//超级管理员
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
        
        //项目仓库
        if (!$this->loginUserInfo['___isSuperAdmin']) {//非 超级管理员
            //判断当前项目以及组织是否已经创建了仓库
            $repoInfo = Bll_ProjectRepoModule_Query::getInstance()->getByProjectIdAndOrgId($this->projectInfo['id'], $this->loginUserInfo['orgId']);
        } else {//超级管理员
            $repoInfo = Bll_ProjectRepoModule_Query::getInstance()->getNewByProjectId($this->projectInfo['id']);
        }
        $this->view->repoInfo = $repoInfo->getResult();
        
        //当前正在使用的分支
        $repoObj = F_Git::open($this->view->repoInfo['repoPath']);
        $result  = $repoObj->getBranchInfoOfLocal();
        $this->view->currentUseBranch = $result['selected'];
        if (empty($this->view->currentUseBranch)) {
            $this->view->currentUseBranch = 'Branch';
        }
        
        //当前正在使用的分支的commit history 列表
        
        //组织列表
        $orgResultSet = Bll_AccountModule_Org::getInstance()->getListOfAll();
        $this->view->orgList = $orgResultSet->getResult();
        
    }
    
}