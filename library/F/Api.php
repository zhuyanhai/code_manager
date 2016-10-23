<?php
/**
 * 框架应用程序外部API基础类
 * 
 * 所有外部可访问的API
 * 
 * @category F
 * @package F_Aapi
 * @author allen <allenifox@163.com>
 * 
 */
final class F_Api
{
    const CLASS_NAME_PREFIX = 'API';

    /**
     * 开始解析接口类
     * 
     */
    public static function run()
    {
        $requestObj    = F_Controller_Request_Http::getInstance();
        $requestMethod = Utils_Validation::filter($requestObj->getParam('sMethod', ''))->removeStr()->removeHtml()->receive();
        if (empty($requestMethod)) {
            //todo log
            throw new Exception('can\'t method in the request params');
        }
        
        $requestMethodArray = explode('.', $requestMethod);
        
        if (count($requestMethodArray) > 2) {
            $module     = $requestMethodArray[0];
            $controller = $requestMethodArray[1];
            $action     = $requestMethodArray[2];
            $className = self::CLASS_NAME_PREFIX . '_' . ucfirst($module) . '_' . ucfirst($controller) . '_' . ucfirst($action);
        } else{
            $controller = $requestMethodArray[0];
            $action     = $requestMethodArray[1];
            $className = self::CLASS_NAME_PREFIX . '_' . ucfirst($controller) . '_' . ucfirst($action);
        }

        try {
            $obj = new $className();
            $obj->init()->run($action);
        } catch (Exception $e) {
            //todo log
            echo $e->getMessage().'  '.$e->getTraceAsString();
        }
        exit;
    }
    
}