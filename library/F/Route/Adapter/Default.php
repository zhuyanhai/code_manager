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

        $module     = 'Index';
        $controller = 'Index';
        $action     = 'Index';

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
            
            // 判断是单数 还是 双数
            if ($pathArrayCount % 2 === 1) {// 单数目录层级 module/module/controller 或 module/controller/action 或 controller
                if ($pathArrayCount > 3) {
                    $dirList = array_splice($pathArray, 0, ($pathArrayCount - ($pathArrayCount - 3)));
                    $checkDirPath  = APPLICATION_CONTROLLER_PATH . '/' . $dirList[0] . '/' . $dirList[1];
                    if (is_dir($checkDirPath)) {
                        $module     = $dirList[0].'_'.$dirList[1];
                        $controller = $dirList[2];
                    } else {
                        $module     = $dirList[0];
                        $controller = $dirList[1];
                        $action     = $dirList[2];
                    }
                } else {
                    $controller = $pathArray[0];
                }
            } else {// 双数目录层级 module/module/controller/action 或 controller/action
                //print_r($pathArray);
                
                $dirList = array_splice($pathArray, 0, 2);
                $checkDirPath  = APPLICATION_CONTROLLER_PATH . '/' . implode($this->_urlDelimiter, $dirList);
                $checkFilePath = APPLICATION_CONTROLLER_PATH . '/' . ucfirst($dirList[0]) . 'Controller.php';
                
                //echo 'checkFilePath=',$checkFilePath,' | checkDirPath=',$checkDirPath;exit;
                
                if (is_file($checkFilePath)) {// controller/action
                    $controller = $dirList[0];
                    $action     = $dirList[1];
                } elseif (is_dir($checkDirPath)) {
                    $tmp = array_splice($pathArray, 0, 2);
                    $module     = $dirList[0].'_'.$dirList[1];
                    $controller = $tmp[0];
                    $action     = $tmp[1];
                } else {
                    throw new F_Route_Exception('format error');
                }
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
}