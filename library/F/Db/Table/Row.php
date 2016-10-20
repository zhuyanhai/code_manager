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
     * 将获取到的 select 数据转换成数组返回
     * 
     * 如果指定需要哪些列,就只转换需要的列,并返回   :其他的列不做任何处理,也不返回
     * 如果没有指定列,转换读取出来的所有列,并返回
     * 
     * @param array $columns 需要使用的列
     */
    public function toArray($columns = array())
    {
        return (array)$this->_data;
    }
}