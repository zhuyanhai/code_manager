<?php
/**
 * 路由器 基类
 * 
 * @category F
 * @package F_Route
 * @author allen <allenifox@163.com>
 */
abstract class F_Route_Adapter_Abstract
{
    /**
     * REQUEST_URI 的分隔符
     * 
     * @var string 
     */
    protected $_urlDelimiter = '/';
    
    /**
     * URI 参数
     * 
     * @var array 
     */
    protected $_params = array();

    /**
     * 构造URI参数
     * 
     * @param array|string $args
     * @param boolean $isQuestionMark true=URI问号后的参数 false=非URI问号后的参数
     */
    protected function _buildParams($args, $isQuestionMark = false)
    {
        if ($isQuestionMark) {//URI问号后的参数
            $tmpParams = explode('&', $args);
            foreach ($tmpParams as $tp) {
                $tpp = explode('=', $tp);
                $key = urldecode($tpp[0]);
                $val = $tpp[1];
                $this->_params[$key] = (isset($this->_params[$key]) ? (array_merge((array) $this->_params[$key], array($val))): $val);
            }
        } else {
            for ($i = 0, $numSegments = count($args); $i < $numSegments; $i = $i + 2) {
                $key = urldecode($args[$i]);
                $val = isset($args[$i + 1]) ? $args[$i + 1] : null;
                $this->_params[$key] = (isset($this->_params[$key]) ? (array_merge((array) $this->_params[$key], array($val))): $val);
            }
        }
        
    }

}