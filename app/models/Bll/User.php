<?php
/**
 * 外部API (外部模块可访问)
 * 
 * 访问权限 - 所有模块均可访问
 * 
 * 用户基本信息 API
 * 
 * @package Bll
 * @subpackage Bll
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_User
{
    /**
     * 用户登陆cookie名字
     */
    const LOGIN_COOKIE_NAME = 'ftoken';
    
    /**
     * 根据 cookie 检测用户是否登录
     * 
     * @return array 用户信息
     */
    public static function checkLogin()
    {
        //登陆cookie内容
        $userLoginCookie = Utils_Cookie::get(self::LOGIN_COOKIE_NAME);
        if (empty($userLoginCookie)) {
           return null; 
        }
        
        //cookie 中记录的信息
        $loginContent = self::_decryptOfLoginCookie($userLoginCookie);
        if (empty($loginContent)) {
            return null; 
        }

        //用户信息
        $userInfo = self::getByUserid($loginContent['userid']);
        if (empty($userInfo)) {
            return null; 
        }
        
        if ($userDao['isLock']) {//用户被锁定
            return null;
        }
        
        return $userInfo;
    }
    
    /**
     * 根据用户ID获取用户信息
     * 
     * @param int $userid 用户ID
     * @return array
     */
    public static function getByUserid($userid)
    {
        return Bll_User_Internal_User::getInstance()->getByUserid($userid);
    }
    
//----- 私有方法
    
    /**
     * 解密登陆cookie
     * 
     * @param string $userLoginCookie
     * @return array
     */
    private static function _decryptOfLoginCookie($userLoginCookie)
    {
        $decryptContent = substr($userLoginCookie, 0, 5);
        $decryptContent.= substr($userLoginCookie, 8, 10);
        $decryptContent.= substr($userLoginCookie, 21, 18);
        $decryptContent.= substr($userLoginCookie, 43);
        
        $lastLoginTime  = substr($userLoginCookie, 5, 3);
        $lastLoginTime .= substr($userLoginCookie, 18, 3);
        $lastLoginTime .= substr($userLoginCookie, 39, 4);
        
        return Utils_EncryptAndDecrypt::timeRange($decryptContent, 'DECODE', Utils_EncryptAndDecrypt::LOGIN_SECRET_KEY, 1800, $lastLoginTime);
    }
}