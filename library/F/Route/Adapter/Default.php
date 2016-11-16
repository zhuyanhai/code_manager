<?php
/**
 * 默认路由器
 * 
 * @category F
 * @package F_Route
 * @author allen <allenifox@163.com>
 */
final class F_Route_Adapter_Default extends F_Route_Adapter_Abstract
{   
    /**
     * 执行
     * 
     * 当没有定义其他路由器时 或 其他路由器的路由规则都不符合时 就会执行默认路由
     * 
     */
    public function exec()
    {
        $requestObj = F_Controller_Request_Http::getInstance();
        
        $uri = $requestObj->getRequestUri();
        $uri = trim($uri);

        if (empty($uri)) {
            throw new F_Route_Exception('When routing uri is empty');
        }

        $module     = 'index';
        $controller = 'index';
        $action     = 'index';

        // 判断请求URI中除了参数就是根[/]
        $isOnlyRoot = FALSE; 

        //分离问号后的请求参数,并检查请求是否只有根
        $splitURI = explode('?', $uri);
        if ($splitURI[0] === '/') {
            $isOnlyRoot = TRUE;
        }

        if ($isOnlyRoot === false) {// 请求示例 http://dox.bxshare.cn/doc/load/a/b 或 http://dox.bxshare.cn/doc/load/?a=b 或 http://dox.bxshare.cn/doc/load?a=b
            
            $splitURI[0] = trim($splitURI[0], '/');
            $pathArray = explode($this->_urlDelimiter, $splitURI[0]);
            $pathArrayCount = count($pathArray);
            
            if ($pathArrayCount === 0) {
                throw new F_Route_Exception('format error');
            }
            
            if ($pathArrayCount === 1 || $pathArrayCount === 2) {
                $checkDirPath1 = APPLICATION_CONTROLLER_PATH . '/' . ucfirst($pathArray[0]);
            } else {
                $checkDirPath1 = APPLICATION_CONTROLLER_PATH . '/' . ucfirst($pathArray[0]) . '/' . ucfirst($pathArray[1]);
                $checkDirPath2 = APPLICATION_CONTROLLER_PATH . '/' . ucfirst($pathArray[0]);
            }
            
            if ($pathArrayCount === 1) {// 请求示例 http://xx.xx.com/a
                if (is_dir($checkDirPath1)) {//目录存在，代表 module
                    $module = $pathArray[0];
                } else {//代表 controller
                    $controller = $pathArray[0];
                }
            } elseif ($pathArrayCount === 2) {//请求示例 http://xx.xx.com/a/b
                if (is_dir($checkDirPath1)) {//目录存在，代表 module_controller
                    $module     = $pathArray[0];
                    $controller = $pathArray[1];
                } else {//代表 controller_action
                    $controller = $pathArray[0];
                    $action     = $pathArray[1];
                }
            }  elseif ($pathArrayCount === 3) {//请求示例 http://xx.xx.com/a/b/c
                if (is_dir($checkDirPath1)) {//目录存在，代表 module_module_controller
                    $module     = $pathArray[0].'_'.$pathArray[1];
                    $controller = $pathArray[2];
                } elseif(is_dir($checkDirPath2)) {//代表 module_controller_action
                    $module     = $pathArray[0];
                    $controller = $pathArray[1];
                    $action     = $pathArray[2];
                }
            } elseif ($pathArrayCount === 4) {//请求示例 http://xx.xx.com/a/b/c/d
                if (is_dir($checkDirPath1)) {//目录存在，代表 module_module_controller_action
                    $module     = $pathArray[0].'_'.$pathArray[1];
                    $controller = $pathArray[2];
                    $action     = $pathArray[3];
                } else {
                    throw new F_Route_Exception('format error');
                }
            } else {
                throw new F_Route_Exception('format error');
            }

            // 设置请求参数
            if (!empty($pathArray)) {
                $this->_buildParams($pathArray);
            }
        }
        
        if (count($splitURI) > 1) {// 设置请求参数
            $this->_buildParams($splitURI[1], true);
        }
        
        if (!empty($this->_params)) {
            $requestObj->setParams($this->_params);
        }

        $requestObj->setModule($module)->setController($controller)->setAction($action);
    }
    
    private function _checkDir()
    {
        
    }
    
    private function _checkFile()
    {
        
    }
}