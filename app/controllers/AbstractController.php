<?php
/**
 * 本项目中所有 controller 必须继承的 controller 基类
 * 
 * 本类中带有逻辑处理
 * - 检测登录 
 */
abstract class AbstractController extends F_Controller_ActionAbstract
{
    /**
     * 登录用户对象
     * 
     * @var array 
     */
    public $loginUserInfo = null;
            
    public function __construct()
    {
        parent::__construct();

        //检测用户登录
        $checkResult = Bll_AccountModule_Login::getInstance()->check();
        if ($checkResult->isError()) {//用户未登录，或用户被锁定等
            
            $controller = $this->_requestObj->getController();
            $module     = $this->_requestObj->getModule();

            if ($module == 'index' && !in_array($controller, array('auth', 'error'))) {//指定不予处理的module/controller
                if ($this->_requestObj->isXmlHttpRequest()) {//是ajax请求
                    $this->error('请先登陆', -110)->response();
                } else {//非ajax
                    $this->_redirectorObj->gotoUrlAndExit('/auth/login/');
                }
            }
        }
        
        $this->view->loginUserInfo = $this->loginUserInfo = $checkResult->getResult();

    }
    
    /**
     * 检测是否登录
     * 
     * @return boolean
     */
    public function isLogin()
    {
        if (empty($this->loginUserInfo) || $this->loginUserInfo['___isLock']) {
            return false;
        }
        return true;
    }
    
    /**
     * action 执行前
     */
    public function preDispatch()
    {
        parent::preDispatch();
    }
    
    /**
     * action 执行后
     */
    public function postDispatch()
    {
        parent::postDispatch();
        
        if (!$this->view->isSetLayout()) {//未设置，设置成默认布局
            $this->view->setLayout('layout_default');
        }
    }
}
