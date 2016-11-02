<?php
/**
 * 前端控制器类
 * 
 * 主要是协调 输入、路由分发、输出 等操作
 * 
 * @category F
 * @package F_Controller
 * @subpackage F_Controller_Front
 * @author allen <allenifox@163.com>
 */
class F_Controller_Front
{
    /**
     * 单例实例
     *
     * @var F_Controller_Front
     */
    protected static $_instance = null;
    
    /**
     * 判断是否截获异常
     * 
     * @var boolean 
     */
    protected $_isInterceptException = true;

    /**
     * 单例模式
     *
     * @return F_Controller_Front
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * 开始处理分配 - 输入、过滤、路由、输出
     */
    public function dispatch()
    {
        try {
            $requestObj  = F_Controller_Request_Http::getInstance();
            $responseObj = F_Controller_Response_Http::getInstance();
            
            //过滤输入
            $this->_filterInput($requestObj, $responseObj);

            F_Route::run();

            do {
                $requestObj->setDispatched(true);
                
                $module     = $requestObj->getModule();
                $controller = $requestObj->getController();
                $action     = $requestObj->getAction();

                if ('index' === strtolower($module)) {
                    $controllerClass = ucfirst($controller) . 'Controller';
                } else {
                    $moduleArray = explode('_', $module);
                    if (count($moduleArray) > 1) {
                        $controllerClass = ucfirst($moduleArray[0]) . '_' . ucfirst($moduleArray[1]) . '_' . ucfirst($controller) . 'Controller';
                    } else {
                        $controllerClass = ucfirst($module) . '_' . ucfirst($controller) . 'Controller';
                    }
                }
                
                $obLevel = ob_get_level();
                ob_start();
                
                try {
                    $controllerObj = new $controllerClass();
                    if (!($controllerObj instanceof F_Controller_ActionAbstract)) {
                        throw new F_Controller_Exception('Controller "' . $controllerClass . '" is not an instance of F_Controller_ActionAbstract');
                    }
                    $controllerObj->dispatch($action);
                } catch (Exception $e) {
                    // Clean output buffer on error
                    $curObLevel = ob_get_level();
                    if ($curObLevel > $obLevel) {
                        do {
                            ob_get_clean();
                            $curObLevel = ob_get_level();
                        } while ($curObLevel > $obLevel);
                    }
                    if ($this->_isInterceptException) {
                        if ($requestObj->getController() !== 'Error') {
                            F_Controller_ErrorHandle::getInstance()->forward($e);
                        } else {
                            throw $e;
                        }
                    } else {
                        throw $e;
                    }
                }
            } while(!$requestObj->isDispatched());
        
        } catch (Exception $e) {//判断是否截获异常
            throw $e;
        }
        
        $responseObj->sendResponse();
    }
    
    /**
     * 过滤所有的输入 $_GET $_POST
     */
    private function _filterInput($requestObj, $responseObj)
    {
        try { 
            $this->_filterProcess('GET', $_GET);
            $this->_filterProcess('POST', $_POST);
        } catch(F_Exception $e) {
            if ($requestObj->isXmlHttpRequest()) {//ajax
                $response = array(
                    'status'        => -1,
                    'data'          => array(),
                    'msg'           => $e->getMessage(),
                );
                $responseObj->setHeader('Content-Type', 'application/json', true);
                $body = json_encode($response);
                $responseObj->setBody($body);
                $responseObj->sendResponseAndExit();
            } else {
                throw new F_Exception($e->getMessage(), $e->getCode());
            }
        }
    }
    
    private function _filterProcess($requestType, $args)
    {
        static $prefix = array('s', 'i', 'f', 'h', 'a');
        
        if (is_array($args) && count($args) > 0) {
            
            foreach ($args as $key=>&$val) {
                if (!in_array($key[0], $prefix) || preg_match('%[a-z]%', $key[1])) {
                    throw new F_Exception("{$requestType} 参数 “{$key}” 类型错误");
                }
                
                switch ($key[0]) {
                    case 's':
                        $val = Utils_Validation::filter($val)->removeHtml()->removeStr()->receive();
                        break;
                    case 'i':
                        if (!is_numeric($val)) {
                            throw new F_Exception("{$requestType} 参数 “{$key}” 类型错误[i]");
                        }
                        $val = intval($val);
                        break;
                    case 'f':
                        if (!preg_match('%^[0-9]*\.[0-9]+$%i', $val)) {
                            throw new F_Exception("{$requestType} 参数 “{$key}” 类型错误[f]");
                        }
                        $val = floatval($val);
                        break;
                    case 'h':
                        $val = Utils_Validation::filter($val)->removeStr()->xss()->receive();
                        break;
                    case 'a':
                        //todo
                        break;
                    default:
                        throw new F_Exception("{$requestType} 参数 “{$key}” 类型错误");
                        break;
                }
            }
        }
    }
}