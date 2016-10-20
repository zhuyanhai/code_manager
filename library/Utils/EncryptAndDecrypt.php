<?php
/**
 * 加密和解密
 *
 *
 * @package Utils
 */
final class Utils_EncryptAndDecrypt
{
    /**
     * 登陆密钥
     */
    const LOGIN_SECRET_KEY = '@%1w#m9ig7x@^(+^$';
    
    /**
     * 指定时间范围内加/解密有效
     * 
     * @param string $string 需要加密/解密的字符串
     * @param string $operation DECODE解密 ENCODE加密
     * @param string $key 公钥
     * @param int $expiry 过期时间 秒
     * @param int $compareTime 比较时间 0=和当前时间比较 >0＝和指定时间比较
     * @return string
     */
    public static function timeRange($string, $operation = 'DECODE', $key = '', $expiry = 3600, $compareTime = 0)
    {
        $ckey_length = 4;
        // 随机密钥长度 取值 0-32;
        // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
        // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
        // 当此值为 0 时，则不产生随机密钥
        $key = md5($key ? $key : '#$&123DFF#@**9el2%s7');
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);       
        $string = $operation == 'DECODE' ? utf8_decode(base64_decode(substr($string, $ckey_length))) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        $string_length = strlen($string);
        
        $result = '';
        $rndkey = $box = array();
        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
            $box[$i] = $i;
        }
        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $tempOrd = chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
            $result .= $tempOrd;
        }
        
        if($operation == 'DECODE') {
            $compareTime = $compareTime?:time();
            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - $compareTime > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc.str_replace('=', '', base64_encode(utf8_encode($result)));
        }
    }
}