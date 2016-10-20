<?php
/**
 * 字符编码处理
 *
 * GBK GB2312 转换成 UTF-8
 *
 * @package Utils
 */
final class Utils_Encoding
{
    /**
     * 检测并转换成 utf-8
     *
     * @param string $string
     * @return string
     */
    public static function checkAndConvertToUtf8($string)
    {
        switch (mb_detect_encoding($string, "UTF-8,GB2312,GBK")) {
            case 'EUC-CN':
                return mb_convert_encoding($string, 'UTF-8', 'GB2312');
                break;
            case 'CP936':
                return mb_convert_encoding($string, 'UTF-8', 'GBK');
                break;
        }
        return $var;
    }
}