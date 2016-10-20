<?php
/**
 * 路由 类
 * 
 * 定义怎样路由到指定的控制器 或 API
 * 路由最多支持"两层"目录
 * 
 * @category F
 * @package F_Route
 * @author allen <allenifox@163.com>
 */
final class F_Route
{
    /**
     * 路由器列表
     * 
     * @var array 
     */
    private static $_routerList = array();
    
    /**
     * 条件
     * 
     * @var string
     */
    private $_condition = '';
    
    /**
     * 匹配路由后的回调方法
     * 
     * @var callback
     */
    private $_callback = '';
    
    /**
     * 特定参数正则条件
     * 
     * @var array
     */
    private $_where = array();
    
    /**
     * 路由器匹配后的操作方式
     * 
     * @var string
     */
    private $_opMode = 'dispatch';
    
    /**
     * 构造函数
     * 
     */
    private function __construct($condition, $callback)
    {
        $this->_condition = $condition;
        $this->_callback  = $callback;
    }
    
    /**
     * 特定参数的正则条件
     * 
     * @param string $name
     * @param string $regexpCondition
     * @return F_Route
     */
    public function where($name, $regexpCondition)
    {
        $this->_where[$name] = $regexpCondition;
        return $this;
    }
    
    /**
     * 当前路由器类的操作是,一旦匹配就终止继续其他路由器的匹配
     * 
     * 这个是默认值
     */
    public function opTermination()
    {
        $this->_opMode = 'exit';
    }
    
    /**
     * 当前路由器类的操作是,一旦匹配就直接跳转到默认路由器继续匹配
     */
    public function opSkipToDefault()
    {
        $this->_opMode = 'toDefault';
    }
    
    /**
     * 当前路由器类的操作是,一旦匹配就直接去前端控制器
     * 
     */
    public function opDispatch()
    {
        $this->_opMode = 'dispatch';
    }
    
    /**
     * 添加新路由器
     * 
     * 执行时有添加的顺序执行,直到最后都没有匹配的,再执行默认路由器
     * 
     * @param string $condition
     * @param callback $callback
     * @return F_Route
     */
    public static function add($condition, $callback)
    {
        $obj = new F_Route($condition, $callback);
        
        self::$_routerList[] = $obj;
        
        return $obj;
    }
    
    /**
     * 启动路由
     * 
     * @throws F_Route_Exception
     */
    public static function run()
    {
        $requestObj = F_Controller_Request_Http::getInstance();

        if (!empty(self::$_routerList)) {
            foreach (self::$_routerList as $router) {
                $routerOfRegExp = new F_Route_Adapter_RegExp();
                $result = $routerOfRegExp->exec($router->_condition, $router->_where, $router->_callback);
                if ($result) {// 匹配
                    echo $router->_opMode;exit;
                    if ($router->_opMode === 'dispatch') {// 直接去前端控制器
                        return;
                    } elseif($router->_opMode === 'exit') {// 终止
                        exit;
                    } else {// 跳转到默认
                        break;
                    }
                }
            }
        }
        
        //默认路由器
        $routerOfDefault = new F_Route_Adapter_Default();
        $routerOfDefault->exec();
    }

}