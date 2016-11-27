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
        
        //组织列表
        $orgResultSet = Bll_AccountModule_Org::getInstance()->getListOfAll();
        $this->view->orgList = $orgResultSet->getResult();
        
        $this->view->currentUseBranch = 'Branch';
        $this->view->history = array();
        $this->view->currentCommitId = null;

        if (!empty($this->view->repoInfo)) {
            //当前正在使用的分支
            $repoObj = F_Git::open($this->view->repoInfo['repoPath']);
            $result  = $repoObj->getBranchInfoOfLocal();
            
            if (!empty($result['selected'])) {
                $this->view->currentUseBranch = $result['selected'];
                if (empty($this->view->currentUseBranch)) {
                    $this->view->currentUseBranch = 'Branch';
                }

                //当前正在使用的分支的commit history 列表
                $history = $repoObj->getCommitHistory(1, 50);
                $this->view->history = $history;

                //最新提交的内容列表
                $this->view->commitContentList = $repoObj->getCommitContentList($history['list'][0]['commitId']);
                $this->view->currentCommitId = $history['list'][0]['commitId'];
        //        print_r($this->view->commitContentList);exit;
        //        print_r($history);
        //        exit;
            }

        }
        
    }
    
    /**
     * 获取某次提交的内容列表
     */
    public function getCommitContentListAction()
    {
        if ($this->isAjax()) {
            $commitIdHash = $this->_requestObj->getParam('sCommitIdHash', '');
            $repoPath = $this->_requestObj->getParam('sRepoPath', '');
            if (empty($commitIdHash) || empty($repoPath)) {
                $this->error('参数错误')->response();
            }
            $repoObj = F_Git::open($repoPath);
            $commitContentList = $repoObj->getCommitContentList($commitIdHash);
            $this->response(array('commitContentList' => $commitContentList));
        }
        exit;
    }
    
    /**
     * 获取某次提交的内容列表中的指定文件的最近两次的差异对比
     */
    public function getCommitContentDiffAction()
    {
        if ($this->isAjax()) {
            $commitIdHash = $this->_requestObj->getParam('sCommitIdHash', '');
            $repoPath = $this->_requestObj->getParam('sRepoPath', '');
            $bl = $this->_requestObj->getParam('iBl', -1);
            $el = $this->_requestObj->getParam('sEl', -1);
            if (empty($commitIdHash) || empty($repoPath) || $bl < 0) {
                $this->error('参数错误')->response();
            }
            if (empty($el)) {
                $el = null;
            }
            $repoObj = F_Git::open($repoPath);
            $diffContent = $repoObj->getDiffOfNearest($commitIdHash, $bl, $el);
            $this->response(array('diffContent' => $diffContent));
        }
        exit;
    }
    
}