<?php
/**
 * 外部接口
 * 
 * 访问权限 - 所有模块均可访问
 * 
 * 用户模块
 * 
 * 用户登录逻辑 - 处理接口
 * 
 * @package Bll
 * @subpackage Bll_UserModule
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_UserModule_Login
{
    private function __construct()
    {
        //empty
    }

    /**
     * 单例模式
     * 
     * 获取类的对象实例
     * 
     * @staticvar Bll_UserModule_Login $instance
     * @return \Bll_UserModule_Login
     */
    public static function getInstance()
    {
        static $instance = null;
        
        if (is_null($instance)) {
            $instance = new self();
        }
        
        return $instance;
    }
    
    /**
     * 检测用户登录 - 根据COOKIE
     * 
     * @return ResultSet
     */
    public function check()
    {
        $userInfoInstance = Bll_UserModule_Internal_Info::getInstance();
        
        //登陆cookie内容
        $loginCookieResultSet = $userInfoInstance->getLoginCookie();
        if ($loginCookieResultSet->isError()) {
            return $loginCookieResultSet;
        }

        //用户信息
        $userResultSet = $userInfoInstance->getByUserid($loginCookieResultSet->userid);
        if ($userResultSet->isError()) {
            return $userResultSet;
        }

        $tokenMatchingResult = $userInfoInstance->checkToken($userResultSet->userid, $userResultSet->passwd, $userResultSet->create_time, $loginCookieResultSet->token);

        if ($tokenMatchingResult->isError()) {//检测token
            return $userResultSet->resetError('请先登录');
        }
        
        if ($userResultSet->___isLock) {//用户被锁定
            return $userResultSet->resetError('用户被锁定');
        }
        
        return $userResultSet;
    }

    /**
     * 处理登录 - 登录页面，用户提交
     * @param string $account 用户登录账号
     * @param string $passwd 用户登录密码
     * @param int $isRemember 是否记住密码 1=记住 0=不记住
     * @return ResultSet
     */
    public function process($account, $passwd, $isRemember = 0)
    {
        if (empty($account)) {
            return F_Result::build()->error('账户不能为空');
        }
        if (empty($passwd)) {
            return F_Result::build()->error('密码不能为空');
        }
        
        $userInfoInstance = Bll_UserModule_Internal_Info::getInstance();
        
        //用户信息
        $userResultSet = $userInfoInstance->getByAccount($account);
        if ($userResultSet->isError()) {
            return F_Result::build()->error('账户或密码错误');
        }
        
        $passwd = $userInfoInstance->buildPassword($passwd, $userResultSet->create_time);
        if ($userResultSet->passwd !== $passwd) {
            return F_Result::build()->error('账户或密码错误');
        }
        
        $userInfoInstance->setLoginCookie($userResultSet->userid, $userResultSet->passwd, $userResultSet->create_time, $isRemember);
        
        return $userResultSet;
    }
}