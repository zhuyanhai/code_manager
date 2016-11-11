<?php
/**
 * 账号管理
 * 
 * -列表显示
 * -添加
 * -删除
 * 
 */
class Admin_AccountController extends AbstractController
{   
    /**
     * 账号列表
     */
    public function indexAction()
    {
        $page = intval($this->_requestObj->getParam('page', 1));
        
        $this->view->searchs = array();
        $this->view->searchs['status']      = $this->_requestObj->getParam('iStatus', 99);
        $this->view->searchs['searchType']  = $this->_requestObj->getParam('sSearchType', 'account');
        $this->view->searchs['searchValue'] = $this->_requestObj->getParam('sSearchValue', '');

        $resultSet = Bll_AccountModule_User::getInstance()->getListOfAdmin($this->view->searchs, $page);
        if ($resultSet->isSuccess()) {
            $this->view->list = $resultSet->getResult();
        } else {
            $this->view->list = null;
        }
    }
    
    /**
     * 添加账号
     */
    public function addAction()
    {
        if ($this->isAjax()) {
            $post = $this->_requestObj->getPost();
            $resultSet = Bll_AccountModule_User::getInstance()->add($post);
            if ($resultSet->isError()) {
                $this->error($resultSet->getErrorInfo())->response();
            }
            $this->response();
        }
                
        $menusResultSet = Bll_PrivilegeModule_Query::getInstance()->getAllOfAdmin();
        if ($menusResultSet->isSuccess()) {
            $this->view->menus = $menusResultSet->getResult();
        } else {
            $this->view->menus = array();
        }
        
    }
    
    /**
     * 编辑账号
     */
    public function editAction()
    {
        if ($this->isAjax()) {
            $post = $this->_requestObj->getPost();
            $resultSet = Bll_AccountModule_User::getInstance()->edit($post);
            if ($resultSet->isError()) {
                $this->error($resultSet->getErrorInfo())->response();
            }
            $this->response();
        }
        
        try {
            $userid = Utils_Validation::verify('iUserid', $this->_requestObj->getParam('iUserid', 0))->required()->receive();
            $userResult = Bll_AccountModule_User::getInstance()->getByUserid($userid);
            if ($userResult->isError()) {
                $userResult->jumpToRefer('/admin/account/');
            }
            $this->view->userInfo = $userResult->getResult();
        } catch (Utils_Validation_Exception $e) {
            $e->jumpToRefer('/admin/account/');
        }
        
        $menusResultSet = Bll_PrivilegeModule_Query::getInstance()->getAllOfAdmin();
        if ($menusResultSet->isSuccess()) {
            $this->view->menus = $menusResultSet->getResult();
        } else {
            $this->view->menus = array();
        }
        
        //获取用户的权限ID数组
        $userPrivilegeIdsResultSet = Bll_PrivilegeModule_User::getInstance()->getIds($userid);
        if ($userPrivilegeIdsResultSet->isSuccess() && !$userPrivilegeIdsResultSet->isEmpty() && !$menusResultSet->isEmpty()) {
            $userPrivilegeIds = $userPrivilegeIdsResultSet->getResult();
            foreach ($this->view->menus as &$menu) {
                if (in_array($menu['id'], $userPrivilegeIds)) {
                    $menu['checked'] = true;
                }
            }
        }
    }
    
    /**
     * 锁定账号
     */
    public function lockAction()
    {
        if ($this->isAjax()) {
            try {
                $userid = Utils_Validation::verify('iUserid', $this->_requestObj->getParam('iUserid', 0))->required()->int()->notZero()->receive();
                $resultSet = Bll_AccountModule_User::getInstance()->lock($userid);
                if ($resultSet->isError()) {
                    $this->error($resultSet->getErrorInfo())->response();
                }
                $this->response();
            } catch(Utils_Validation_Exception $e) {
                $this->error('锁定失败')->response();
            }
        }
    }
    
    /**
     * 解锁账号
     */
    public function unlockAction()
    {
        if ($this->isAjax()) {
            try {
                $userid = Utils_Validation::verify('iUserid', $this->_requestObj->getParam('iUserid', 0))->required()->int()->notZero()->receive();
                $resultSet = Bll_AccountModule_User::getInstance()->unlock($userid);
                if ($resultSet->isError()) {
                    $this->error($resultSet->getErrorInfo())->response();
                }
                $this->response();
            } catch(Utils_Validation_Exception $e) {
                $this->error('解锁失败')->response();
            }
        }
    }
}