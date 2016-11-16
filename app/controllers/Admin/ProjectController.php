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
        $page = intval($this->_requestObj->getParam('page', 1));
        
        $this->view->searchs = array();
        $this->view->searchs['status']      = $this->_requestObj->getParam('iStatus', 99);
        $this->view->searchs['searchType']  = $this->_requestObj->getParam('sSearchType', 'id');
        $this->view->searchs['searchValue'] = $this->_requestObj->getParam('sSearchValue', '');

        $resultSet = Bll_ProjectModule_Query::getInstance()->getListOfAdmin($this->view->searchs, $page);
        if ($resultSet->isSuccess()) {
            $this->view->list = $resultSet->getResult();
        } else {
            $this->view->list = null;
        }
    }
    
    /**
     * 添加项目
     */
    public function addAction()
    {
        if ($this->isAjax()) {
            $post = $this->_requestObj->getPost();
            $resultSet = Bll_ProjectModule_Operation::getInstance()->add($post);
            if ($resultSet->isSuccess()) {
                $this->response();
            }
            $this->error($resultSet->getErrorInfo())->response();
        }
    }
    
    /**
     * 编辑项目
     */
    public function editAction()
    {
        if ($this->isAjax()) {
            $post = $this->_requestObj->getPost();
            $resultSet = Bll_ProjectModule_Operation::getInstance()->edit($post);
            if ($resultSet->isSuccess()) {
                $this->response();
            }
            $this->error($resultSet->getErrorInfo())->response();
        }
        
        $id = $this->_requestObj->getParam('iId', 0);
        if (empty($id)) {
            $this->_redirectorObj->gotoUrlAndExit('/admin/project/');
        }
        $projectResultSet = Bll_ProjectModule_Query::getInstance()->getById($id);
        if ($projectResultSet->isError()) {
            $this->_redirectorObj->gotoUrlAndExit('/admin/project/');
        }
        
        $this->view->project = $projectResultSet->getResult();
    }
    
    /**
     * 删除项目
     */
    public function delAction()
    {
        if ($this->isAjax()) {
            $id = $this->_requestObj->getParam('iId', 0);
            $resultSet = Bll_ProjectModule_Operation::getInstance()->del($id);
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
            $resultSet = Bll_ProjectModule_Operation::getInstance()->revert($id);
            if ($resultSet->isSuccess()) {
                $this->response();
            }
            $this->error()->response();
        }
    }
    
}