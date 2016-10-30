<?php
/**
 * 结果 类
 * 
 * 任何业务接口的 public方法 的返回值都必须使用 本类来 构造和检测
 * 
 * @category F
 * @package F_Result
 * @author allen <allenifox@163.com>
 */
final class F_Result
{
    private function __construct()
    {
        //empty
    }

    /**
     * 构建要返回的结果
     * 
     * @staticvar F_Result $instance
     * @return \F_Result
     */
    public static function build()
    {
        static $instance = null;
        
        if (is_null($instance)) {
            $instance = new self();
        }
        
        return $instance;
    }

    /**
     * 构建的结果是成功的
     * 
     * @param mixed $data
     * @return \ResultSet
     */
    public function success($data = '')
    {
        return new ResultSet(array('status' => 1, 'data' => $data));
    }
    
    /**
     * 构建错误的结果集
     * 
     * @param string $errorMsg 错误信息
     * @param int $statusCode 错误编号，必须 <0
     * @return \ResultSet
     * @throws ResultException
     */
    public function error($errorMsg = '', $statusCode = -1)
    {
        if (intval($statusCode) >= 0) {
            throw new ResultException('构建错误结果集失败，statusCode 错误');
        }

        return new ResultSet(array('status' => $statusCode, 'errorMsg' => $errorMsg, 'data' => null));
    }
}

/**
 * 结果集 异常
 *
 */
final class ResultException extends F_Exception
{
    function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

/**
 * 结果集
 */
final class ResultSet
{
    /**
     * 结果集
     * 
     * @var array
     */
    private $_resultSet = array();
    
    /**
     * 结果状态 true=成功 false=失败
     * 
     * @var boolean
     */
    private $_status = false;
    
    /**
     * 构造函数
     * 
     * @param array $resultSet
     * @throws ResultException
     */
    public function __construct($resultSet)
    {
        if (!is_array($resultSet) || !isset($resultSet['status'])) {
            throw new ResultException('初始化结果集失败');
        }
        
        if (intval($resultSet['status']) === 1) {//成功
            $this->_status = true;
        } else {
            $this->_status = false;
        }

        if ($this->_status && !isset($resultSet['data'])) {//成功，但是没有设置data
            throw new ResultException('初始化结果集失败，data 丢失');
        }

        if (!$this->_status && !isset($resultSet['errorMsg'])) {//失败，但是没有设置errorMsg
            throw new ResultException('初始化结果集失败，errorMsg 丢失');
        }
        
        $this->_resultSet = $resultSet;
    }
    
    /**
     * 检测结果是否成功
     * 
     * @return boolean true=成功 false=出错
     */
    public function isSuccess()
    {
        if ($this->_status) {//成功
            return true;
        }
        
        return false;
    }
    
    /**
     * 检测结果是否出错
     * 
     * @return boolean true=出错 false=成功
     */
    public function isError()
    {
        if (!$this->_status) {//出错
            return true;
        }
        
        return false;
    }
    
    /**
     * 如果正确 - 获取结果
     * 
     * @return mixed
     */
    public function getResult()
    {
        return $this->_resultSet['data'];
    }
    
    /**
     * 如果出错 - 获取错误信息
     * 
     * @return string
     */
    public function getErrorInfo()
    {
        return $this->_resultSet['errorMsg'];
    }
    
    /**
     * 重置错误信息
     * 
     * @param string $errorMsg 错误信息
     * @param int $statusCode 错误编号，必须 <0
     * @return \ResultSet
     * @throws ResultException
     */
    public function resetError($errorMsg, $statusCode = -1)
    {
        if (intval($statusCode) >= 0) {
            throw new ResultException('构建错误结果集失败，statusCode 错误');
        }
        
        $this->_resultSet['status']   = $statusCode;
        $this->_resultSet['errorMsg'] = $errorMsg;
        $this->_status = false;
        return $this;
    }
    
    /**
     * 获取 _resultSet 中的 data 中的信息
     * 
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (!is_array($this->_resultSet['data'])) {
            throw new ResultException('data 中没有 ['.$name.'] 这个值');
        }
        
        if (!isset($this->_resultSet['data'][$name])) {
            throw new ResultException('data 中没有 ['.$name.'] 这个值');
        }
        
        return $this->_resultSet['data'][$name];
    }
}