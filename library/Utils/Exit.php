<?php
/**
 * 截断程序运行 － 工具集合
 *
 * 当程序逻辑判断不允许程序继续运行，或同时需要停止和打印一些提示信息
 *
 * @package Utils
 */
final class Utils_Exit
{
    public static function stop()
    {
        exit;
    }
    
    public static function stopAndOutput($outputStr, $encoding = 'utf-8')
    {
        self::_outHeaderEncoding($encoding);
        echo $outputStr;
        exit;
    }
    
    /**
     * 输出页面编码，在没有layout的情况下
     *
     * @param string $encoding 编码值
     */
    private static function _outHeaderEncoding($encoding = 'utf-8')
    {
        header('Content-type:text/html; charset='.$encoding);
    }
}