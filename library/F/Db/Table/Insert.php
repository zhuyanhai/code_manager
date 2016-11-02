<?php
/**
 * DB insert 类
 *
 * - 专门负责 insert 的所有构造操作
 */
final class F_Db_Table_Insert
{
    /**
     * 数据表配置
     * 
     * @var array
     */
    private $_tableConfigs = array();

    
    /**
     * 获取类实例
     *
     * @staticvar F_Db_Table_Insert $instance
     * @return \F_Db_Table_Insert
     */
    public static function getInstance()
    {
        static $instance = null;
        if(null == $instance){
            $instance = new self();
        }
        return $instance;
    }
    
    /**
     * 插入行记录
     * 
     * @param array $rowData 插入行的内容
     * @return string 返回插入成功后的主键值
     */
    public function insert($rowData)
    {
        $pdo       = F_Db::getInstance()->changeConnectServer('master');
        $dbName    = $this->_tableConfigs['dbFullName'];
        $tableName = $this->_tableConfigs['tableName'];
        $lastId    = $pdo->insert($rowData, $dbName.'.'.$tableName);
        return $lastId;
    }
    
    /**
     * 初始化数据表配置
     * 
     * @param array $tableConfigs 数据表配置
     * @return \F_Db_Table_Insert
     */
    public function ___initTableConfigs($tableConfigs)
    {
        $this->_tableConfigs = $tableConfigs;
        
        return $this;
    }
    
}