<?php
/**
 * 搜索引擎 蜘蛛爬行|机器人 检测
 *
 * @package Utils
 */
final class Utils_Robot
{
    /**
     * 检测是否是搜索蜘蛛爬行
     *
     * @return string|boolean false 不是蜘蛛
     */
    public static function check()
    {
        $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);

        if (strpos($userAgent, 'googlebot') !== false){
            return 'Google';
        }

        if (strpos($userAgent, 'baiduspider') !== false){
            return 'Baidu';
        }

        if (strpos($userAgent, 'msnbot') !== false){
            return 'Bing';
        }

        if (strpos($userAgent, 'slurp') !== false){
            return 'Yahoo';
        }

        if (strpos($userAgent, 'sosospider') !== false){
            return 'Soso';
        }

        if (strpos($userAgent, 'sogou spider') !== false){
            return 'Sogou';
        }

        if (strpos($userAgent, 'yodaobot') !== false){
            return 'Yodao';
        }

        return false;
    }

}