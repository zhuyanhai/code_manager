<?php
/**
 * 时间处理 - 工具集合
 *
 *
 * @package Utils
 */
final class Utils_Date
{
    /**
     * 获取 microtime(true) * 10000 后的值
     * 
     * @return int
     */
    public static function microtime()
    {
        return intval(microtime(true) * 10000);
    }
}