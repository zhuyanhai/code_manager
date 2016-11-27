<?php
/**
 * 仓库管理首页
 * 
 */
class Project_RepoController extends Project_AbstractController
{
    /**
     * 添加项目仓库
     */
    public function addAction()
    {
        if ($this->isAjax()) {
            if (!$this->loginUserInfo['___isSuperAdmin']) {//超级管理员
                $this->error('您没有权限创建仓库')->response();
            }
            $post = $this->_requestObj->getPost();
            $post['iProjectId'] = $this->projectInfo['id'];
            $resultSet = Bll_ProjectRepoModule_Operation::getInstance()->add($post);
            if ($resultSet->isSuccess()) {
                //仓库创建完毕,自动创建master分支
                Bll_ProjectCodeModule_Operation::getInstance()->add(array(
                    'iProjectId' => $post['iProjectId'],
                    'iType'      => 1,
                    'sName'      => '主分支',
                    'sIntro'     => '主分支',
                ));
                $this->response();
            }
            $this->error($resultSet->getErrorInfo())->response();
        }
    }
    
}