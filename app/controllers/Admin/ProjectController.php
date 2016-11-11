<?php
/**
 * 项目管理
 * 
 */
class Admin_ProjectController extends AbstractController
{   
    /**
     * 项目列表
     */
    public function indexAction()
    {

    }
    
    /**
     * 添加项目
     */
    public function addAction()
    {
        if ($this->isAjax()) {
            $post = $this->_requestObj->getPost();
            $resultSet = Bll_PrivilegeModule_Operation::getInstance()->add($post);
            if ($resultSet->isSuccess()) {
                $this->response();
            }
            $this->error($resultSet->getErrorInfo())->response();
        }
        
        $this->view->privilegeList = Bll_PrivilegeModule_Query::getInstance()->getListOfByUserid(0);
    }
    
    /**
     * 编辑项目
     */
    public function editAction()
    {
        if ($this->isAjax()) {
            $post = $this->_requestObj->getPost();
            $resultSet = Bll_PrivilegeModule_Operation::getInstance()->edit($post);
            if ($resultSet->isSuccess()) {
                $this->response();
            }
            $this->error($resultSet->getErrorInfo())->response();
        }
        
        $id = $this->_requestObj->getParam('iId', 0);
        if (empty($id)) {
            $this->_redirectorObj->gotoUrlAndExit('/admin/project/');
        }
        $privilegeResultSet = Bll_PrivilegeModule_Query::getInstance()->getById($id);
        if ($privilegeResultSet->isError()) {
            $this->_redirectorObj->gotoUrlAndExit('/admin/project/');
        }
        
        $this->view->privilege = $privilegeResultSet->getResult();
        $this->view->privilegeList = Bll_PrivilegeModule_Query::getInstance()->getListOfByUserid(0);
    }
    
    /**
     * 删除项目
     */
    public function delAction()
    {
        if ($this->isAjax()) {
            $id = $this->_requestObj->getParam('iId', 0);
            $resultSet = Bll_PrivilegeModule_Operation::getInstance()->del($id);
            if ($resultSet->isSuccess()) {
                $this->response();
            }
            $this->error()->response();
        }
    }
    
    /**
     * 恢复项目
     */
    public function revertAction()
    {
        if ($this->isAjax()) {
            $id = $this->_requestObj->getParam('iId', 0);
            $resultSet = Bll_PrivilegeModule_Operation::getInstance()->revert($id);
            if ($resultSet->isSuccess()) {
                $this->response();
            }
            $this->error()->response();
        }
    }
    
}