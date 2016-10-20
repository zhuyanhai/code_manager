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
     * @var type 
     */
    public $loginUser = null;
            
    public function __construct()
    {
        parent::__construct();
        
        //检测用户登录
        //$this->loginUser = Bll_Passport::checkLogin();
        
//        $a = Dao_User_PassportPhone::getSelect()->fromColumns('userid')->where('userid=:userid', 1)->fetchRow();
//        print_r($a->toArray());
//        $a = Dao_User_PassportPhone::getSelect()->where('userid=:userid', 1)->fetchAll();
//        print_r($a);
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
    
    /**
     * 检查是否登陆
     * 
     * -未登陆，非ajax，跳转到登陆页
     * -未登陆，ajax，直接给客户端返回未登陆标记
     */
    public function checkLoginAndGoto()
    {
        if (empty($this->loginUser)) {//用户未登陆，跳转到登陆页，如果时ajax，就直接返回
            if ($this->_requestObj->isXmlHttpRequest()) {//是ajax
                $this->error('请先登陆', -110)->response();
            } else {//非ajax
                $this->_redirectorObj->gotoUrlAndExit('/login/');
            }
        }
        
        return true;
    }
}
