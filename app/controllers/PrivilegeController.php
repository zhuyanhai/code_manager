<?php
/**
 * 权限管理
 * 
 * -列表显示
 * -添加
 * -删除
 * 
 */
class PrivilegeController extends AbstractController
{   
    /**
     * 权限列表
     */
    public function indexAction()
    {

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