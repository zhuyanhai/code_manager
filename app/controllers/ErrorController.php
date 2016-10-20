<?php
/**
 * 全站异常控制
 * 
 * - 正常的异常
 * - 人为控制维护跳转
 */
class ErrorController extends F_Controller_ActionAbstract
{
    /**
     * 抛出未接收的异常时
     * 
     * - 系统异常
     * - 人为异常
     * - 站点维护时异常【code=999】
     */
    public function errorAction()
    {
        $error = $this->_requestObj->getParam('errorHandler');

        if (!$error instanceof Exception) {//非异常类对象，有问题，按默认404处理
            echo $error->getTraceAsString();
        } else {
            switch (intval($error->getCode())) {
                case 403://无权限
                    
                    break;
                case 404://找不到指定的
                    
                    break;
                case 500://系统错误
                    if (!Utils_EnvCheck::isProduction()) {//非正式环境
                        F_Application::getInstance()->getWhoops()->handleException($error);
                    }
                    break;
                case 999://站点维护
                    
                    break;
                default://抛异常时没有定义错误编号
                    if (!Utils_EnvCheck::isProduction()) {//非正式环境
                        F_Application::getInstance()->getWhoops()->handleException($error);
                    }
                    break;
            }
        }
        Utils_Exit::stop();
    }
    
}