<?php
/**
 * DB row 数据公共处理 类
 *
 * - 专门负责 select 获取到的单行数据行的 公共处理操作
 */
class F_Db_Table_Row
{
    /**
     * select 读取出来的数据
     * 
     * @var array
     */
    private $_data = array();
    
    /**
     * 构造函数
     * 
     * @param array $data
     */
    public function __construct($data)
    {
        $this->_data = $data;
    }
    
    /**
     * 获取字段内容
     *
     * @param  string $columnName 指定的字段名
     * @return string             The corresponding column value.
     * @throws F_Db_Exception
     */
    public function __get($columnName)
    {
        $columnName = $this->_transformColumn($columnName);
        if (!array_key_exists($columnName, $this->_data)) {
            throw new F_Db_Exception("指定的字段名 \"$columnName\" 不存在");
        }
        return $this->_data[$columnName];
    }

    /**
     * 设置一个字段值
     *
     * @param  string $columnName 字段名
     * @param  mixed  $value      字段值
     * @return void
     * @throws F_Db_Exception
     */
    public function __set($columnName, $value)
    {
        $columnName = $this->_transformColumn($columnName);
        if (!array_key_exists($columnName, $this->_data)) {
            throw new F_Db_Exception("指定的字段名 \"$columnName\" 不存在");
        }
        $this->_data[$columnName] = $value;
    }

    /**
     * 删除字段
     *
     * @param  string $columnName 字段名
     * @return F_Db_Table_Row
     * @throws F_Db_Exception
     */
    public function __unset($columnName)
    {
        $columnName = $this->_transformColumn($columnName);
        if (!array_key_exists($columnName, $this->_data)) {
            throw new F_Db_Exception("指定的字段名 \"$columnName\" 不存在");
        }
        unset($this->_data[$columnName]);
        return $this;
    }

    /**
     * 检测字段是否存在
     *
     * @param string $columnName 字段名
     * @return boolean
     */
    public function __isset($columnName)
    {
        $columnName = $this->_transformColumn($columnName);
        return array_key_exists($columnName, $this->_data);
    }
    
    /**
     * 将获取到的 select 数据转换成数组返回
     * 
     * 如果指定需要哪些列,就只转换需要的列,并返回   :其他的列不做任何处理,也不返回
     * 如果没有指定列,转换读取出来的所有列,并返回
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->_format();
    }
    
       
   /**
     * 格式化当前数据表内容
     * 
     * - 不允许调用任何业务模块和接口来格式化数据
     * - 仅是根据数据本身来处理
     * 
     * @param array $columns 指定格式化的字段数组
     * @return array
     */
   protected function _format()
   {
        $formatData = array();
        foreach ($this->_data as $fieldName=>$fieldVal) {
            $isFun   = 'is'.ucfirst($fieldName);
            $getFun  = 'get'.ucfirst($fieldName);
            $showFun = 'show'.ucfirst($fieldName);
            
            if (method_exists($this, $isFun)) {
                $formatData[$isFun] = $this->$isFun();
            }
            
            if (method_exists($this, $getFun)) {
                $formatData[$getFun] = $this->$getFun();
            }
            
            if (method_exists($this, $showFun)) {
                $formatData[$showFun] = $this->$showFun();
            }

            $formatData[$fieldName] = $fieldVal;
        }
        
        return $formatData;
   }
   
   /**
     * 检测字段名类型
     *
     * @param string $columnName 字段名字
     * @return string
     * @throws F_Db_Exception 如果字段名不是一个字符串
     */
    protected function _transformColumn($columnName)
    {
        if (!is_string($columnName)) {
            throw new F_Db_Exception('指定的字段名不是字符串类型');
        }
        return $columnName;
    }
}