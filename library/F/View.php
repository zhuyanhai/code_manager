<?php
/**
 * 框架应用程序视图基础类
 * 
 * @category F
 * @package F_View
 * @author allen <allenifox@163.com>
 * 
 */
final class F_View
{
    /**
     * 单例实例
     * 
     * @var F_View 
     */
    private static $_instance = null;
    
    /**
     * 布局路径
     * 
     * @var string
     */
    protected $_layoutPath = '';
    
    /**
     * 是否使用布局
     * 
     * @var boolean
     */
    protected $_isUseLayout = true;
    
    /**
     * 视图配置
     * 
     * @var array
     */
    private $_configs = null;
            
    private function __construct()
    {
        $this->_configs = F_Config::get('application.view');
    }
    
    /**
     * 单例模式
     * 
     * @return F_View
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new F_View();
        }
        
        return self::$_instance;
    }
    
    /**
     * 获取当前设置的布局文件名，相对application.cfg.php中设置的layout的路径
     *  
     * @return string
     */
    public function getLayout()
    {
        return $this->_layoutPath;
    }
    
    /**
     * 设置布局文件名，相对application.cfg.php中设置的layout的路径
     *  
     * @param string $filename 首位不要有【/】，正确写法是：admin/a，尾部不需要后缀
     */
    public function setLayout($filename)
    {
        $this->_layoutPath = $filename;
    }
    
    /**
     * 判断是否设置了layout
     * 
     * return boolean true=已设置 false=未设置
     */
    public function isSetLayout()
    {
        if (empty($this->_layoutPath)) {
            return false;
        }
        return true;
    }

    /**
     * 禁用布局
     * 
     */
    public function disableLayout()
    {
        $this->_isUseLayout = false;
    }
    
    /**
     * 启用布局
     * 
     */
    public function enableLayout()
    {
        $this->_isUseLayout = true;
    }
    
    /**
     * 在视图中继续渲染视图
     * 
     * @param string $path 首位不要有【/】，正确写法是：admin/a，尾部不需要后缀
     * @return string
     */
    public function render($path)
    {
        $filename = $this->_configs['scriptPath'];
        $filename .= $path . '.phtml';

        if (!file_exists($filename)) {
            $filename = $this->_configs['layoutPath'];
            $filename .= $path . '.phtml';
            if (!file_exists($filename)) {
                throw new F_View_Exception('View ['. $filename .'] not found');
            }
        }

        ob_start();
        include $filename;
        $scriptContent = ob_get_clean();
        return $scriptContent;
    }
    
    /**
     * 解析视图
     * 
     * @param string $filename action对应的视图文件全路径，包括文件名
     */
    public function parse()
    {         
        $requestObject = F_Controller_Request_Http::getInstance();
        
        $module     = $requestObject->getModule();
        $controller = $requestObject->getController();
        $action     = $requestObject->getAction();
        
        $filename = $this->_configs['scriptPath'];
        if (strtolower($module) !== 'index') {
            $filename .= lcfirst($module) . '/';
        }
        $filename .= lcfirst($controller) . '/' . lcfirst($action) . '.phtml';
        
        if (!file_exists($filename)) {
            throw new F_View_Exception('View ['. $filename .'] not found');
        }
        header('Content-Type: text/html; charset=' . $this->_configs['charset']);
                
        ob_start();
        include $filename;
        $scriptContent = ob_get_clean();

        if ($this->_isUseLayout) {//使用布局
            ob_start();
            include $this->_configs['layoutPath'] . $this->_layoutPath . '.phtml';
            $scriptContent = ob_get_clean();
        }

        $responseObject = F_Controller_Response_Http::getInstance();
        $responseObject->setBody($scriptContent);
    }
    
    /**
     * 获取未定义的成员
     *
     * @param  string $key
     * @return null
     */
    public function __get($name)
    {
        return null;
    }

    /**
     * 检测成员是否存在
     * 
     * 当未定义的变量调用 empty 或 isset
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        if ('_' != substr($name, 0, 1)) {
            return isset($this->$name);
        }

        return false;
    }
    
    /**
     * 构造需要在视图中使用的数据
     * 
     * @param string $name
     * @param mixed $value
     * @return void
     * @throws F_View_Exception
     */
    public function __set($name, $value)
    {
        if ('_' != substr($name, 0, 1)) {
            $this->$name = $value;
            return;
        }
        
        throw new F_View_Exception('不允许设置私有和受保护的View类成员');
    }
    
    /**
     * Allows unset() on object properties to work
     *
     * @param string $name
     * @return void
     */
    public function __unset($name)
    {
        if ('_' != substr($name, 0, 1) && isset($this->$name)) {
            unset($this->$name);
        }
    }
    
    /**
     * 魔术方法 - 调用 view 层助手
     * 
     * @param string $name
     * @param array $arguments
     */
    public function __call($name, $arguments)
    {
        static $helpers = array();

        $class = 'C_View_Helper_' . ucfirst($name);
        $checkTier = 2;
        do {
            $isWhileCall = false;
            if(!isset($helpers[$class])){
                try {
                    $checkTier--;
                    $helpers[$class] = new $class();
                } catch(F_Application_Exception $e) {
                    if ($checkTier <=0 ) {
                        throw new F_Application_Exception($e->getMessage(), $e->getCode());
                    }
                    if (5555 === intval($e->getCode())) {
                        $isWhileCall = true;
                        $class = 'F_View_Helper_' . ucfirst($name);
                    }
                }
            }
        } while ($isWhileCall);
        
        if(!method_exists($helpers[$class], $name)){
            throw new F_Exception('view helper “'.$class.'” method “'.$name.'” not found');
        }

        return call_user_func_array(array($helpers[$class], $name), $arguments);
    }
}