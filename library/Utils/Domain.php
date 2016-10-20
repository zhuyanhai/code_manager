<?php
/**
 * 转换域名
 * 
 * 传入指定的域名标识，输出指定的域名
 *
 * @package Utils
 */
final class Utils_Domain
{
    /**
     * 根据站点标识 - 获取带有协议的主机名 例如：http://doc.bxshare.cn
     * 
     * @param string $domainFlag 站点标识，例如：doc 等
     * @param string $protol 协议类型，例如：http | https 等
     * @return string
     */
    public static function get($domainFlag, $protol = 'http')
    {
        $domainConfigs = F_Config::get('application.domain');
        return (isset($domainConfigs[$domainFlag]))?$protol.'://'.$domainConfigs[$domainFlag]:null;
    }
}