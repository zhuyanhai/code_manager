<?php
/**
 * 转换域名
 * 
 * 传入指定的域名标识，输出指定的域名
 *
 * @author allen <allen@yuorngcorp.com>
 * @package C_View
 */
final class C_View_Helper_Domain
{
    /**
     * 
     * @param type $domainFlag
     * @return type
     */
    public function domain($domainFlag)
    {
        return Utils_Domain::get($domainFlag);
    }
    
}