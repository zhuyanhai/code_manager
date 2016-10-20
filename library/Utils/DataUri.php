<?php
/**
 * 关于转换成 data uri 形式展示
 *
 * 转换的可以是 图片 或 其他类型文件
 *
 * @package Utils
 */
final class Utils_DataUri
{
    /**
     * 转换成 data uri 形式
     *
     * @param string $file 具体文件路径，使用绝对路径
     * @param string $mime 文件类型，HTTP中展示形式，例如：image/jpeg
     * @return string
     */
    public static function exec($file, $mime)
    {
        $contents = file_get_contents($file);
        $base64   = base64_encode($contents);
        return "data:$mime;base64,$base64";
    }
}