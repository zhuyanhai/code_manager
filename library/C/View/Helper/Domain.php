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
     * 获取域名
     * 
     * @param string $domainFlag
     * @return string
     */
    public function domain($domainFlag)
    {
        return Utils_Domain::get($domainFlag);
    }
    
    /**
     * 获取关于页面级的 css 静态文件域名与根目录
     * 
     * @return string
     */
    public function getAssetOfPageCss()
    {
        return Utils_Domain::get('code') . '/asset/css/pages/';
    }
    
    /**
     * 获取关于公共模块级的 css 静态文件域名与根目录
     * 
     * @return string
     */
    public function getAssetOfModuleCss()
    {
        return Utils_Domain::get('code') . '/asset/css/modules/';
    }
    
    /**
     * 获取关于页面级的 js 静态文件域名与根目录
     * 
     * @return string
     */
    public function getAssetOfPageJs()
    {
        return Utils_Domain::get('code') . '/asset/js/pages/';
    }
    
    /**
     * 获取关于公共模块级 js 静态文件域名与根目录
     * 
     * @return string
     */
    public function getAssetOfModuleJs()
    {
        return Utils_Domain::get('code') . '/asset/js/modules/';
    }
}