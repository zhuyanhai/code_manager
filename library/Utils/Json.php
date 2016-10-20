<?php
/**
 * JSON 处理
 *
 * 关于 json 的转换 和 检测是否是json
 *
 * @package Utils
 */
final class Utils_Json
{
    /**
     * json_encode 封装
     *
     * 添加判断 php5.4 版本，默认使用新特性 json 的unicode编码，json_encode后不再是\u8899，而是中文
     *
     * @param string $val
     * @param int|null $options
     * @return string
     */
    public static function encode($val, $options = null)
    {
        if(null !== $options){
            return json_encode($val, $options);
        }
        if(PHP_VERSION_ID >= 50400){//php 5.4
            return json_encode($val, JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode($val);
        }
    }

    /**
     * json_decode 封装
     *
     * @param string $val
     * @param boolean $assoc true array | false object【默认】
     * @return mixed
     */
    public static function decode($val, $assoc = false)
    {
        return json_decode($val, $assoc);
    }

    /**
     * 检测字符串是否是json格式
     *
     * @param string $val
     * @return boolean true 字符串【是】json格式 | false 字符串【不是】json格式
     */
    public static function has($val)
    {
        return !is_null(json_decode($val));
    }
}