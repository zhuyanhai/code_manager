<?php
/**
 * 环境监测
 *
 * - 可通过PHP配置判断环境【线上|线下】
 * - 判断是否是cli方式执行
 *
 * @package Utils
 */
final class Utils_EnvCheck
{
    /**
     * 是否是线上环境
     *
     * @return boolean true 是 false 不是
     */
    public static function isProduction()
    {
        if(APPLICATION_ENV == 'production'){
            return true;
        }
        return false;
    }

    /**
     * 是否是开发环境
     *
     * @return boolean true 是 false 不是
     */
    public static function isDevelopment()
    {
        if(APPLICATION_ENV == 'development'){
            return true;
        }
        return false;
    }
    
    /**
     * 判断是否是 cli 方式执行脚本
     * 
     * @return boolean
     */
    public static function isCli()
    {
        return (php_sapi_name() === 'cli') ? true : false;
    }
}