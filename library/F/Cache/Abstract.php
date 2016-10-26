<?php
/**
 * 缓存 抽象 类
 * 
 * @category F
 * @package F_Cache
 * @subpackage F_Cache_Abstract
 * @author allen <allenifox@163.com>
 */
abstract class F_Cache_Abstract 
{
    /**
     * 获取某服务的缓存对象实例
     * 
     * 每个服务 $serviceName 都是单例模式
     * 
     * @param string $serviceName
     */
    abstract protected static function getInstance($serviceName);
    
    /**
     * Available options
     *
     * @var array available options
     */
    protected $_options = array();
    
    /**
     * Constructor
     *
     * @param  array $options Associative array of options
     * @throws Zend_Cache_Exception
     * @return void
     */
    public function __construct(array $options = array())
    {
        while (list($name, $value) = each($options)) {
            $this->setOption($name, $value);
        }
    }
    
    /**
     * Set an option
     *
     * @param  string $name
     * @param  mixed  $value
     * @throws Zend_Cache_Exception
     * @return void
     */
    public function setOption($name, $value)
    {
        if (!is_string($name)) {
            throw new F_Cache_Exception('不正确的选项名字：'.$name);
        }
        $name = strtolower($name);
        if (array_key_exists($name, $this->_options)) {
            $this->_options[$name] = $value;
        }
    }

    /**
     * Returns an option
     *
     * @param string $name Optional, the options name to return
     * @throws Zend_Cache_Exceptions
     * @return mixed
     */
    public function getOption($name)
    {
        $name = strtolower($name);

        if (array_key_exists($name, $this->_options)) {
            return $this->_options[$name];
        }

        throw new F_Cache_Exception('不正确的选项名字：'.$name);
    }

}
