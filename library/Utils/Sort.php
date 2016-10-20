<?php
/**
 * 排序 － 工具集合
 *
 *
 * @package Utils
 *
 */
final class Utils_Sort
{
    /**
     * 用户自定义排序 - $ary数组值是引用传递
     *
     * @param array &$ary 需要排序的数组
     * @param string $field 需要使用排序的key
     * @param string $order asc 升序 desc 降序
     */
    public static function usort(&$ary, $field, $order = 'asc')
    {
        if($order == 'asc'){
            usort($ary, function($a, $b)use($field){
                $al = $a[$field];
                $bl = $b[$field];
                if ($al == $bl) {
                    return 0;
                }
                return ($al > $bl) ? +1 : -1;
            });
        } else {
            usort($ary, function($a, $b)use($field){
                $al = $a[$field];
                $bl = $b[$field];
                if ($al == $bl) {
                    return 0;
                }
                return ($al > $bl) ? -1 : +1;
            });
        }
    }
    
    /**
     * 选择排序法
     * 
     * @param array $arr
     * @param string $field 需要作为比较依据的下标参数
     * @param string $order asc 升序 desc 降序
     * @return array
     */
    public static  function selectSort($arr, $field, $order = 'asc')
    {
        for($i = 0, $len = count($arr); $i < $len-1; $i++) {
            $p = $i;
            for($j = $i+1; $j < $len; $j++) {
                if ($order === 'asc') {
                    if($arr[$p][$field] > $arr[$j][$field]) {
                        $p = $j;
                    }
                } else {
                    if($arr[$p][$field] < $arr[$j][$field]) {
                        $p = $j;
                    }
                }
            }
            if($p != $i) {
                $tmp = $arr[$p];
                $arr[$p] = $arr[$i];
                $arr[$i] = $tmp;
            }
        }
        return $arr;
    }
    
    /**
     * 冒泡排序法
     * 
     * @param array $arr
     * @param string $field 需要作为比较依据的下标参数
     * @param string $order asc 升序 desc 降序
     * @return array
     */
    public static function getpao($arr, $field, $order = 'asc')
    {  
        $len = count($arr);
        $tmp = '';
        for ($i = 1;$i < $len; $i++) {
            for ($k = 0;$k < $len-$i; $k++) {
                if ($order === 'asc') {
                    if ($arr[$k][$field] > $arr[$k+1][$field]) {
                        $tmp=$arr[$k+1];
                        $arr[$k+1]=$arr[$k];
                        $arr[$k]=$tmp;
                    }
                } else {
                    if ($arr[$k][$field] < $arr[$k+1][$field]) {
                        $tmp=$arr[$k+1];
                        $arr[$k+1]=$arr[$k];
                        $arr[$k]=$tmp;
                    }
                }
            }
        }
        return $arr;
    }
}
