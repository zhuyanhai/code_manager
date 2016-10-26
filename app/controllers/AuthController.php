<?php
/**
 * 用户认证
 * 
 * -登录
 * -注册
 * 
 */
class AuthController extends AbstractController
{   
    public function preDispatch()
    {
        parent::preDispatch();
        
        $this->view->setLayout('layout_empty');
    }
    
    /**
     * 登录页
     */
    public function loginAction()
    {
        if ($this->_requestObj->isPost()) {
            $this->view->error = '';
            $account  = Utils_Validation::filter($this->_requestObj->getParam('account', ''))->removeStr()->removeHtml()->receive();
            $passwd   = Utils_Validation::filter($this->_requestObj->getParam('passwd', ''))->removeStr()->removeHtml()->receive();
            $remember = intval($this->_requestObj->getParam('remember', 0));
            $userResult = Bll_UserModule_Login::getInstance()->process($account, $passwd, $remember);
            if ($userResult->isError()) {//错误
                $this->view->error = $userResult->getErrorInfo();
            } else {
                $refer = Utils_Session::get('login_refer');
                $this->_redirectorObj->gotoUrlAndExit($refer);
            }
        } else {
            $refer = Utils_Session::get('login_refer');
            if (empty($refer)) {
                $refer = '/index/';
            }
            $refer = Utils_Http::getReferer($refer, '%utan\.com/auth/(login|register)%i');
            Utils_Session::set('login_refer', $refer);
            
            if ($this->isLogin()) {
                $this->_redirectorObj->gotoUrlAndExit($refer);
            }
        }
    }
}