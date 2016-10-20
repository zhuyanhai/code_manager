<?php
/**
 * 框架应用程序最基础的类
 * 
 * @category F
 * @package F_Application
 * @author allen <allenifox@163.com>
 */
final class F_Application
{
    /**
     * 应用程序全局配置
     * 
     * @var array
     */
    private $_configs = array();
    
    /**
     * Bootstrap 类实例
     * 
     * @var Bootstrap 
     */
    private $_bootstrapObj = null;
    
    /**
     * 调试错误类对象实例
     * 
     * @var \Whoops\Run
     */
    private $_whoops = null;
    
    /**
     * 自动加载的命名空间数组
     * 
     * @var array 
     */
    private static $_autoloadNamespaces = array();
    
    /**
     * 单例实例
     * 
     * @var F_Application 
     */
    private static $_instance = null;
    
    /**
     * 单例模式
     * 
     * @return F_Application
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new F_Application();
        }
        
        return self::$_instance;
    }

    /**
     * 构造函数
     * 
     * 初始化应用程序必备的
     * - 全局配置
     * - autoloader
     * 
     * @throws F_Exception
     */
    private function __construct()
    {
        //设置默认时区
        date_default_timezone_set('Asia/Chongqing');
        
        // 设定 - 站点程序目录
        defined('APPLICATION_PATH') || define('APPLICATION_PATH', ROOT_PATH . '/app');
        
        // 设定 - 站点程序控制器目录
        defined('APPLICATION_CONTROLLER_PATH') || define('APPLICATION_CONTROLLER_PATH', APPLICATION_PATH . '/controllers');

        // 设定 - 站点环境变量
        defined('APPLICATION_ENV') || define('APPLICATION_ENV', TMS_ENVIRON);
        
        // 设定 - 站点类库目录
        defined('LIBRARY_PATH') || define('LIBRARY_PATH', ROOT_PATH . '/library/');

        //初始化PHP查找文件的路径
        $this->_initIncludePath();
        
        //设置自动加载处理方法
        spl_autoload_register('F_Application::autoload');
        
        //定义自动加载命名空间
        self::$_autoloadNamespaces = array(
            'F_' => LIBRARY_PATH,
            'C_' => LIBRARY_PATH,
            'T_' => LIBRARY_PATH,
            'Utils_' => LIBRARY_PATH,
            'Bll_' => APPLICATION_PATH . '/models/',
            'Dao_' => APPLICATION_PATH . '/models/',
            'Controller_' => APPLICATION_CONTROLLER_PATH . '/',
            'EAPI_' => APPLICATION_PATH . '/eapis/',
        );
        
        //初始化全局配置
        F_Config::load('/configs/application.cfg.php');
        $this->_configs = F_Config::get('application');
        
        //合并自动加载命名空间
        if (isset($this->_configs['autoloaderNamespaces'])) {
            self::$_autoloadNamespaces = array_merge(self::$_autoloadNamespaces, $this->_configs['autoloaderNamespaces']);
        }

        if (!Utils_EnvCheck::isCli()) {//非cli方式执行脚本
            
            if (!Utils_EnvCheck::isProduction()) {//非正式环境
                //初始化PHP错误捕获与调试
                $this->_initErrorCapture();
            }

            if (!isset($_SERVER['HTTP_HOST'])) {
                throw new F_Application_Exception('HTTP_HOST notfound');
            }
            
            $this->_configs['cookie']['domain'] = $this->_getCookieDomain();
        } else {
            $this->_configs['cookie']['domain'] = '';
        }
        
    }
    
    /**
     * spl_autoload 处理类的自动加载
     * 
     * @param string $class 需自动加载的类名
     * @return boolean
     */
    public static function autoload($class)
    {
		// 检查类 或 接口 是否已经定义
		if (class_exists($class, false) || interface_exists($class, false)) {
			return false;
		}
        
        if (preg_match('%Controller$%', $class)) {//controller
            $classArray = array('Controller');
        } else {
            $classArray = explode('_', $class);
        }

        if (!isset(self::$_autoloadNamespaces[$classArray[0].'_'])) {
            throw new F_Application_Exception('Can\'t find '.$classArray[0].'_ in the autoloadNamespaces', 6666);
        }

        // 自动组织类路径
        switch ($classArray[0]) {
            case 'EAPI':
                $classArray = explode('_', $class);
                $namespace = $classArray[0];
                unset($classArray[0]);
                $file = self::$_autoloadNamespaces[$namespace.'_'] . implode(DIRECTORY_SEPARATOR, $classArray) . '.php';
                break;
            default:
                $file = self::$_autoloadNamespaces[$classArray[0].'_'] . strtr($class, '_', DIRECTORY_SEPARATOR) . '.php';
                break;
        }

        // 检查文件是否存在
		if(false === file_exists($file)){
            throw new F_Application_Exception('文件 ['.$file.'] 不存在', 5555);
		} else {
			require $file;
		}
    }
    
    /**
     * 获取错误调试类对象
     * 
     * @return \Whoops\Run
     */
    public function getWhoops()
    {
        return $this->_whoops;
    }
    
    /**
     * 引导程序
     * 
     * @return F_Application
     */
    public function bootstrap()
    {
        if (isset($this->_configs['phpSettings'])) {//设置php.ini
            foreach ($this->_configs['phpSettings'] as $k => $v) {
                ini_set($k, $v);
            }
        }
        
        require $this->_configs['bootstrap']['path'];
        $this->_bootstrapObj = new $this->_configs['bootstrap']['class']();
        return $this;
    }
    
    /**
     * 开始运行整个框架机制
     */
    public function run()
    {
        $frontObj = F_Controller_Front::getInstance();
        $response = $frontObj->dispatch();
    }
    
//--- 私有方法
    
    /**
     * 初始化PHP查找文件的路径
     * 
     * @return void
     */
    private function _initIncludePath()
    {
        set_include_path(implode(PATH_SEPARATOR, array(
            realpath(APPLICATION_PATH),
            realpath(APPLICATION_CONTROLLER_PATH),
            realpath(LIBRARY_PATH),
            get_include_path(),
        )));
    }
    
    /**
     * 初始化PHP错误捕获与调试
     * 
     * @return void 
     */
    private function _initErrorCapture()
    {
        require_once APPLICATION_PATH . '/../library/T/Whoops/autoload.php';
        $this->_whoops = new \Whoops\Run;
        if (F_Controller_Request_Http::getInstance()->isXmlHttpRequest()) {//ajax 请求
            $this->_whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler);
        } else {
            $this->_whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        }
        $this->_whoops->register();
    }
    
    /**
     * 获取cookie域名
     * 
     * @return string
     */
    private function _getCookieDomain()
    {
        if (!isset($_SERVER['HTTP_HOST'])) {
            return '';
        }
        
        $cookeDomain = $_SERVER['HTTP_HOST'];
        //域名数组
        $domainArray = explode('.', $_SERVER['HTTP_HOST']);
        //域名级数
        $domainCount = count($domainArray);
        if ($domainCount > 3) {
            if (in_array($domainArray[$domainCount-1], array('com','cn','net')) && 
                in_array($domainArray[$domainCount-2], array('com','cn','net'))) {// xx.xx.com.cn 或 xx.xx.xx.com.cn
                $cookeDomain = $domainArray[$domainCount-3].'.'.$domainArray[$domainCount-2].'.'.$domainArray[$domainCount-1];
            } else {// xx.xx.xx.com
                $cookeDomain = $domainArray[$domainCount-2].'.'.$domainArray[$domainCount-1];
            }
        } else if($domainCount == 3){
            $cookeDomain = $domainArray[1].'.'.$domainArray[2];
        } else {
            $cookeDomain = $domainArray[0].'.'.$domainArray[1];
        }
        return '.' . $cookeDomain;
    }
}