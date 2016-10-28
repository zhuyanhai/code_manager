<?php
/**
 * 通用 工具类
 * 
 * 无法归类的
 * 
 * @package Utils
 */
final class Utils_Utility
{
    /**
     * 将下划线转换成驼峰式
     * 
     * @param mixed<string|array> $data
     * @param boolean $ucfirst
     * @return mixed<string|array>
     */
    public static function convertUnderline($data , $ucfirst = false)
    {
        if (!is_string($data)) {
            $return = array();
            foreach ($data as $key=>$str) {
                $key = preg_replace_callback('/(([-_]+)([A-Za-z]{1}))/i',function($matches){
                    if ($matches[2] === '_') {
                        return strtoupper($matches[3]);
                    }
                    return $matches[0];
                }, $key);
                $key = $ucfirst ? ucfirst($key) : $key;
                $return[$key] = $str;
            }
            return $return;
        } else {
            $data = preg_replace_callback('/(([-_]+)([A-Za-z]{1}))/i',function($matches){
                if ($matches[2] === '_') {
                    return strtoupper($matches[3]);
                }
                return $matches[0];
            }, $data);
            return $ucfirst ? ucfirst($data) : $data;
        }
    }
    
    /**
     * 将下划线转换成驼峰式 - 关于二维数组的
     * 
     * @param mixed<string|array> $list
     * @param boolean $ucfirst
     * @return mixed<string|array>
     */
    public static function convertUnderlineOfTwoDimArray($list , $ucfirst = false)
    {
        foreach ($list as $k=>$data) {
            $list[$k] = self::convertUnderline($data, $ucfirst);
        }
        
        return $list;
    }
}