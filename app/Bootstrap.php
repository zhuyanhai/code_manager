<?php
/**
 * 引导脚本
 */
class Bootstrap extends F_Application_BootstrapAbstract
{   
    /**
     * 初始路由设置
     * 
     * - 添加任意自定义路由
     */
    public function _route()
    {
        require APPLICATION_PATH . '/bootstraps/routes.php';
    }

}
