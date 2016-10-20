<?php
/**
 * 检测请求的工具类
 * 
 * - 检测请求是否来至PC端
 * - 检测请求是否来至移动端
 * - 检测请求是否来至平板
 * 
 * @package Utils
 */
final class Utils_CheckRequest
{
    /**
     * 请求是否来至PC
     * 
     * return boolean true是 false否
     */
    public static function isFromPc()
    {
        $tMobileDetectObj = F_MobileDetect::getInstance();
        return (!$tMobileDetectObj->isMobile() && !$tMobileDetectObj->isTablet())? true:false;
    }
    
    /**
     * 请求是否来至移动端
     * 
     * return boolean true是 false否
     */
    public static function isFromMobile()
    {
        return (F_MobileDetect::getInstance())? true:false;
    }
    
    /**
     * 请求是否来至平板电脑
     * 
     * return boolean true是 false否
     */
    public static function isFromTablet()
    {
        return (F_MobileDetect::getInstance()->isTablet())? true:false;
    }
    
    /**
     * 请求是否来至微信
     * 
     * return boolean true是 false否
     */
    public static function isFromWechat()
    {
        if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
           return true;
        } else {
            return false;
        }
    }
}