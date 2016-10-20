<?php
/**
 * 密码强度检测
 *
 * @package Utils
 */
final class Utils_PasswordStrong
{
    /**
     * 基本检测适配器
     *
     * @param string $password 密码
     * @return int
     */
    private static function _baseAdapter($password)
    {
        /*
         * count_chars 返回字符串所用字符的信息
         *
         * 统计 string 中每个字节值（0..255）出现的次数，使用多种模式返回结果。可选参数 mode 默认值为 0。根据不同的 mode ，count_chars() 返回下列不同的结果：
         *
         *   0 - 以所有的每个字节值作为键名，出现次数作为值的数组。
         *   1 - 与 0 相同，但只列出出现次数大于零的字节值。
         *   2 - 与 0 相同，但只列出出现次数等于零的字节值。
         *   3 - 返回由所有使用了的字节值组成的字符串。
         *   4 - 返回由所有未使用的字节值组成的字符串。
         */

        $h    = 0;
        $size = strlen($password);
        foreach(count_chars($password, 1) as $v){
            $p = $v / $size;
            $h -= $p * log($p) / log(2);
        }
        $strength = ($h / 4) * 100;
        if($strength > 100){
            $strength = 100;
        }
        return $strength;
    }

    /**
     * 检测密码强度
     *
     * @param string $password 密码
     * @param string $adapter 适配器可以自行增加
     * @return int
     */
    public static function check($password, $adapter = 'base')
    {
        $adapter = '_' . $adapter;
        return self::$adapter($password);
    }

}