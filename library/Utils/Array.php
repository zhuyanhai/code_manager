<?php
/**
 * 各种数组 工具
 * 
 */
class Utils_Array
{
   /**
     * 将数组或对象中的指定下标中的内容，提炼成一维数组
     * 
     * @param object|array $array
     * @param string $field
     * @return array
     */
    public static function toFlat($array, $field = null)
    {
        $field = $field?:'id';
        $rs = array();
        foreach($array as $t) {
            $rs[] = is_array($t) ? $t[$field] : $t->$field;
        }
        return $rs;
    }
    
    /**
     * 将数组或对象中的指定字段设为为key和value，提炼成一维数组
     * 
     * @param object|array $iterator
     * @param string $fieldKey 数组下标
     * @param string $fieldVal 数组下标的值
     * @param boolean $isArray
     * @return array
     */
    public static function toKV(&$iterator, $fieldKey = null, $fieldVal = null, $isArray = false)
    {
        $rs = array();
        foreach($iterator as $t) {
            $k = is_array($t) ? $t[$fieldKey] : $t->$fieldKey;
            $v = is_array($t) ? $t[$fieldVal] : $t->$fieldVal;
            if($isArray){
                if(array_key_exists($k, $rs)){
                    $rs[$k][] = $v;
                } else {
                    $rs[$k] = array($v);
                }
            } else {
                $rs[$k] = $v;
            }
        }
        return $rs;
    }
    
    /**
     * 使用原有数组中的值做为新数组的key，创建一个新数组返回
     * 
     * @param object|array $iterator
     * @param string $fieldKey 数组下标
     * @param boolean $isArray
     * @return array
     */
    public static function toArrayByNewKey(&$iterator, $fieldKey = null, $isArray = false)
    {
        $rs = array();
        foreach($iterator as $t) {
            $k = is_array($t) ? $t[$fieldKey] : $t->$fieldKey;
            if($isArray){
                if(array_key_exists($k, $rs)){
                    $rs[$k][] = $t;
                } else {
                    $rs[$k] = array($t);
                }
            } else {
                $rs[$k] = $t;
            }
        }
        return $rs;
    }
}