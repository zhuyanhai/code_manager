<?php
/**
 * 权限管理
 * 
 * -列表显示
 * -添加
 * -删除
 * 
 */
class Admin_PrivilegeController extends AbstractController
{   
    /**
     * 权限列表
     */
    public function indexAction()
    {

    }
    
    /**
     * 添加权限
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
     * 编辑权限
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
            $this->_redirectorObj->gotoUrlAndExit('/admin/privilege/');
        }
        $privilegeResultSet = Bll_PrivilegeModule_Query::getInstance()->getById($id);
        if ($privilegeResultSet->isError()) {
            $this->_redirectorObj->gotoUrlAndExit('/admin/privilege/');
        }
        
        $this->view->privilege = $privilegeResultSet->getResult();
        $this->view->privilegeList = Bll_PrivilegeModule_Query::getInstance()->getListOfByUserid(0);
    }
    
    /**
     * 删除权限
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
     * 恢复权限
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