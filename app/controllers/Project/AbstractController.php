<?php
/**
 * 本项目中所有 controller 必须继承的 controller 基类
 * 
 * 本类中带有逻辑处理
 * - 检测登录 
 */
abstract class Project_AbstractController extends AbstractController
{
    /**
     * 项目信息
     * 
     * @var array
     */
    protected $projectInfo = null;
            
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * action 执行前
     */
    public function preDispatch()
    {
        parent::preDispatch();
        
        $id = $this->_requestObj->getParam('iProjectId', 0);
        if (empty($id)) {
            $this->_redirectorObj->gotoUrlAndExit('/');
        }
        
        $projectResultSet = Bll_ProjectModule_Query::getInstance()->getById($id);
        if ($projectResultSet->isError() || $projectResultSet->isEmpty()) {
            $this->_redirectorObj->gotoUrlAndExit('/');
        }
        
        $this->projectInfo = $projectResultSet->getResult();
        $this->projectInfo['pageUrl'] = '/project/?iProjectId=' . $this->projectInfo['id'];
        $this->view->projectInfo = $this->projectInfo;
    }
}
