<?php
/**
 * 验证和过滤 类
 *
 * 各种验证处理，例如：required int email 等
 *
 */
final class Utils_Validation
{
    /**
     * 类实例
     * 
     * @var Utils_Validation 
     */
    private static $_instance = null;
    
    /**
     * 校验类
     * 
     * @var Utils_Validation_VerifyTools 
     */
    private $_verifyToolsInstance = null;
    
    /**
     * 过滤类
     * 
     * @var Utils_Validation_FilterTools 
     */
    private $_filterToolsInstance = null;
    
    private function __construct()
    {
        $this->_verifyToolsInstance = new Utils_Validation_VerifyTools();
        $this->_filterToolsInstance = new Utils_Validation_FilterTools();
    }

    /**
     * 获取单例
     *
     * @staticvar null $instance
     * @return \Utan_Utils_Validation
     */
    private static function _getInstance()
    {
        if(is_null(self::$_instance)){
            self::$_instance = new Utils_Validation();
        }
    }
    
    /**
     * 校验内容
     * 
     * @param string $paramKey 需校验的变量名字
     * @param mixed $paramVal 需校验的变量值
     * @return Utils_Validation_VerifyTools
     */
    public static function verify($paramKey, $paramVal)
    {
        if(is_null(self::$_instance)){
            self::_getInstance();
        }
        return self::$_instance->_verifyToolsInstance->init(Utils_Validation_VerifyTools::MODEL_VERIFY, $paramKey, $paramVal);
    }
    
    /**
     * 过滤内容
     * 
     * @param mixed $paramVal
     * @return Utan_Utils_Validation_FilterTools
     */
    public static function filter($paramVal)
    {
        if(is_null(self::$_instance)){
            self::_getInstance();
        }
        return self::$_instance->_filterToolsInstance->init($paramVal);
    }
    
    /**
     * 仅检测内容，返回正确与错误
     * 
     * @param mixed $paramVal 需校验的变量值
     * @return Utils_Validation_VerifyTools
     */
    public static function test($paramVal)
    {
        if(is_null(self::$_instance)){
            self::_getInstance();
        }
        return self::$_instance->_verifyToolsInstance->init(Utils_Validation_VerifyTools::MODEL_TEST, '', $paramVal);
    }
}