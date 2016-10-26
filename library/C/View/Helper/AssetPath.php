<?php
/**
 * 静态资产路径
 *
 * @author allen <allen@yuorngcorp.com>
 * @package C_View
 */
final class C_View_Helper_AssetPath
{   
    /**
     * 获取静态资产路径
     * 
     * @return \C_View_Helper_AssetPath
     */
    public function assetPath()
    {
        return $this;
    }
    
    /**
     * 获取关于页面级的 css 静态文件域名与根目录
     * 
     * @param string $path
     * @return string
     */
    public function getCssOfPage($path)
    {
        return Utils_Domain::get('code') . '/asset/css/pages/'.$path.'.css';
    }
    
    /**
     * 获取关于公共模块级的 css 静态文件域名与根目录
     * 
     * @param string $path
     * @return string
     */
    public function getCssOfModule($path)
    {
        $pathArray = explode(',', $path);
        if (count($pathArray) > 1) {
            foreach ($pathArray as &$p) {
                $p = '/modules/' . $p;
            }
            $path = implode(',', $pathArray);
            return Utils_Domain::get('code') . '/asset/css/??'.$path;
        }
        return Utils_Domain::get('code') . '/asset/css/modules/'.$path.'.css';
    }
    
    /**
     * 获取关于页面级的 js 静态文件域名与根目录
     * 
     * @param string $path
     * @return string
     */
    public function getJsOfPage($path)
    {
        return Utils_Domain::get('code') . '/asset/js/pages/'.$path.'.js';
    }
    
    /**
     * 获取关于公共模块级 js 静态文件域名与根目录
     * 
     * @param string $path
     * @return string
     */
    public function getJsOfModule($path)
    {
        $pathArray = explode(',', $path);
        if (count($pathArray) > 1) {
            foreach ($pathArray as &$p) {
                $p = '/modules/' . $p;
            }
            $path = implode(',', $pathArray);
            return Utils_Domain::get('code') . '/asset/js/??'.$path;
        }
        return Utils_Domain::get('code') . '/asset/js/modules/'.$path.'.js';
    }
}