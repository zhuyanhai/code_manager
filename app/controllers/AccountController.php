<?php
/**
 * 账号管理
 * 
 * -列表显示
 * -添加
 * -删除
 * 
 */
class AccountController extends AbstractController
{   
    /**
     * 账号列表
     */
    public function indexAction()
    {
        $page = intval($this->_requestObj->getParam('page', 1));
        
        $this->view->searchs = array();
        $this->view->searchs['status']      = intval($this->_requestObj->getParam('status', 99));
        $this->view->searchs['searchType']  = Utils_Validation::filter($this->_requestObj->getParam('searchType', 'account'))->removeStr()->removeHtml()->receive();
        $this->view->searchs['searchValue'] = Utils_Validation::filter($this->_requestObj->getParam('searchValue', ''))->removeStr()->removeHtml()->receive();

        $this->view->list = Bll_AccountModule_User::getInstance()->getListOfAdmin($this->view->searchs, $page);
    }
    
    /**
     * 添加账号
     */
    public function addAction()
    {
        
    }
    
    /**
     * 编辑账号
     */
    public function editAction()
    {
        
    }
}