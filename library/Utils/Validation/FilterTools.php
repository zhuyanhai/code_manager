<?php
/**
 * 过滤工具集合
 *
 * 实现各种过滤的方法
 */
final class Utils_Validation_FilterTools
{   
    /**
     * 需要过滤的内容
     * 
     * @var mixed 
     */
    private $_val = null;
    
    /**
     * 在执行过滤前必须调用的，请勿在外部调用
     * 
     * @param mixed $content
     * @return \Utils_Validation_FilterTools
     */
    public function init($content)
    {
        $this->_val = $content;
        return $this;
    }
    
    /**
     * 接收全部过滤完后内容
     * 
     * @return mixed
     */
    public function receive()
    {
        return $this->_val;
    }
    
    /**
     * 保留html标签，去除 或 编码特殊字符。剔除ASCII 32以下字符
     * 
     * @return \Utils_Validation_FilterTools
     */
    public function removeStr()
    {
        if (gettype($this->_val) === 'string') {
            $this->_val = filter_var($this->_val, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
        }
        return $this;
    }
    
    /**
     * 如果存在HTML标签，剔除所有标签，保留其中文本，并且转换一些特殊字符成其他形式【浏览器可识别形式 &lt;】
     * 
     * @return \Utils_Validation_FilterTools
     */
    public function removeHtml()
    {
        if (gettype($this->_val) === 'string') {
            $this->_val = filter_var($this->_val, FILTER_SANITIZE_STRING);
        }
        return $this;
    }
    
    /**
     * 去除[\t | 空格]
     * 
     * @return \Utils_Validation_FilterTools
     */
    public function removeEmpty()
    {
        if (gettype($this->_val) === 'string') {
            $trans = array(
                "\t" => '',
                " " => '',
                "　" => '',
            );
            $this->_val = strtr($this->_val, $trans);
        }
        return $this;
    }
    
    /**
     * 移除 &#[a-z0-9A-Z]+; 例如：&#063;
     * 
     * @return \Utils_Validation_FilterTools
     */
    public function removeCode()
    {
        if (gettype($this->_val) === 'string') {
            $this->_val = preg_replace('/(&#[a-zA-Z0-9]+;)*/i', '', $this->_val);
        }
        return $this;
    }
    
    /**
     * 如果存在HTML标签，将作为文本输出，并且转换一些特殊字符成其他形式【浏览器可识别形式 &#60;】
     * 
     * @return \Utils_Validation_FilterTools
     */
    public function convertChar()
    {
        if (gettype($this->_val) === 'string') {
            $trans = array(
                "'" => '&#39;',
                '"' => '&#34;',
                '(' => '&#40;',
                ')' => '&#41;',
                '?' => '&#63;',
            );
            $this->_val = strtr($this->_val, $trans);
        }
        return $this;
    }
    
    /**
     * 转换一些特殊字符成中文形式
     * 
     * @return \Utils_Validation_FilterTools
     */
    public function convertToChinese()
    {
        if (gettype($this->_val) === 'string') {
            $trans = array(
                "'" => "’",
                '"' => '“',
                ',' => '，',
            );
            $this->_val = strtr($this->_val, $trans);
        }
        return $this;
    }
    
    /**
     * 把换行转换成<br>
     * 
     * @return \Utils_Validation_FilterTools
     */
    public function convertSpace()
    {
        if (gettype($this->_val) === 'string') {
            $this->_val = nl2br($this->_val);
            $trans = array(
                '\r\n'=> '<br/>',
            );
            $this->_val = strtr($this->_val, $trans);
        }
        return $this;
    }
    
    /**
     * xss
     * 
     * @param Closure $config 操作配置的处理函数
     * @return \Utils_Validation_FilterTools
     */
    public function xss($config = null)
    {
        require_once(LIBRARY_PATH . '/T/HTMLPurifier/HTMLPurifier.auto.php');
        
        $configInstance = \HTMLPurifier_Config::create($config instanceof \Closure ? null : $config);
        $configInstance->autoFinalize = false;
        $purifier = \HTMLPurifier::instance($configInstance);
        $purifier->config->set('Cache.SerializerPath', '/data/runtime');
        
        if ($config instanceof \Closure) {
            call_user_func($config, $configInstance);
        }
        
        $configInstance->set('Core.Encoding', 'UTF-8');
        //html标签禁用的属性
        $configInstance->set('HTML.ForbiddenAttributes', array('class'));
        
        $this->_val = $purifier->purify($this->_val);
        
        return $this;
    }
}