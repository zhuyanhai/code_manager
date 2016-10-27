<?php
/**
 * DB row 集合 数据公共处理 类
 *
 * - 专门负责 select 获取到的多行数据行的 公共处理操作
 */
class F_Db_Table_RowSet implements SeekableIterator, Countable, ArrayAccess
{
    /**
     * select 读取出来的数据行
     * 
     * @var array
     */
    private $_rows = array();
    
    /**
     * How many data rows there are.
     *
     * @var integer
     */
    protected $_count;
    
    /**
     * 构造函数
     * 
     * @param array $data
     */
    public function __construct($row)
    {
        $this->_rows = $row;
        $this->_count = count($row);
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
        $data = array();
        foreach ($this->_rows as $i => $row) {
            $data[$i] = $row->toArray();
        }
        return (array)$data;
    }
    
    /**
     * 获取记录总数
     * 
     * @return int
     */
    public function count()
    {
        return $this->_count;
    }
    
    /**
     * Check if an offset exists
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->_rows[(int) $offset]);
    }

    /**
     * Get the row for the given offset
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     * @return F_Db_Row
     */
    public function offsetGet($offset)
    {
        $offset = (int) $offset;
        if ($offset < 0 || $offset >= $this->_count) {
            throw new F_Db_Exception("Illegal index $offset");
        }
        $this->_pointer = $offset;

        return $this->current();
    }

    /**
     * Does nothing
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * Does nothing
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
    }
    
    /**
     * Take the Iterator to position $position
     * Required by interface SeekableIterator.
     *
     * @param int $position the position to seek to
     * @return F_Db_Rowset
     * @throws F_Db_Exception
     */
    public function seek($position)
    {
        $position = (int) $position;
        if ($position < 0 || $position >= $this->_count) {
            throw new F_Db_Exception("Illegal index $position");
        }
        $this->_pointer = $position;
        return $this;
    }
    
    /**
     * Rewind the Iterator to the first element.
     * Similar to the reset() function for arrays in PHP.
     * Required by interface Iterator.
     *
     * @return F_Db_Rowset Fluent interface.
     */
    public function rewind()
    {
        $this->_pointer = 0;
        return $this;
    }

    /**
     * Return the current element.
     * Similar to the current() function for arrays in PHP
     * Required by interface Iterator.
     *
     * @return F_Db_Row current element from the collection
     */
    public function current()
    {
        if ($this->valid() === false) {
            return null;
        }

        return $this->_rows[$this->_pointer];
    }

    /**
     * Return the identifying key of the current element.
     * Similar to the key() function for arrays in PHP.
     * Required by interface Iterator.
     *
     * @return int
     */
    public function key()
    {
        return $this->_pointer;
    }

    /**
     * Move forward to next element.
     * Similar to the next() function for arrays in PHP.
     * Required by interface Iterator.
     *
     * @return void
     */
    public function next()
    {
        ++$this->_pointer;
    }

    /**
     * Check if there is a current element after calls to rewind() or next().
     * Used to check if we've iterated to the end of the collection.
     * Required by interface Iterator.
     *
     * @return bool False if there's nothing more to iterate over
     */
    public function valid()
    {
        return $this->_pointer >= 0 && $this->_pointer < $this->_count;
    }
}