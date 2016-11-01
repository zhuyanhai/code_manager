<?php
/**
 * 外部接口
 * 
 * 访问权限 - 所有模块均可访问
 * 
 * 用户模块
 * 
 * 用户登出逻辑 - 处理接口
 * 
 * @package Bll
 * @subpackage Bll_AccountModule
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_AccountModule_Logout
{
    private function __construct()
    {
        //empty
    }

    /**
     * 单例模式
     * 
     * 获取类的对象实例
     * 
     * @staticvar Bll_AccountModule_Logout $instance
     * @return \Bll_AccountModule_Logout
     */
    public static function getInstance()
    {
        static $instance = null;
        
        if (is_null($instance)) {
            $instance = new self();
        }
        
        return $instance;
    }
    
    /**
     * 处理登出
     * 
     * @return void
     */
    public function process()
    {
        Bll_AccountModule_Internal_Info::getInstance()->delLoginCookie();
    }
}